<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumo_agua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medidor')->constrained('medidor')->onDelete('cascade');
            $table->dateTime('fecha_hora');

            // Campos según manual Dragino
            $table->decimal('totalizador_m3', 12, 3);   // Acumulado en m³
            $table->decimal('flow_l_min', 8, 3)->nullable(); // Flujo instantáneo (L/min)
            $table->integer('bateria')->nullable(); // % batería
            $table->json('flags')->nullable(); // Flags de estado

            // Campo calculado (diferencia entre totalizadores)
            $table->decimal('consumo_intervalo_m3', 10, 4)->nullable();

            $table->enum('tipo_registro', ['transmision', 'manual'])->default('transmision');
            $table->timestamps();

            // Índices para mejor performance
            $table->index(['id_medidor', 'fecha_hora']);
            $table->index('fecha_hora');
        });

        // Crear los triggers SIN DELIMITER
        $this->createTriggers();
    }

    public function down(): void
    {
        $this->dropTriggers();
        Schema::dropIfExists('consumo_agua');
    }

    private function createTriggers(): void
    {
        // Trigger 1: Calcular consumo_intervalo_m3 automáticamente
        DB::unprepared("
            DROP TRIGGER IF EXISTS before_insert_calcular_consumo;
            CREATE TRIGGER before_insert_calcular_consumo
            BEFORE INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE prev_totalizador DECIMAL(12,3);
                
                -- Obtener el último totalizador del mismo medidor
                SELECT totalizador_m3 INTO prev_totalizador
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                ORDER BY fecha_hora DESC
                LIMIT 1;
                
                -- Calcular diferencia
                IF prev_totalizador IS NOT NULL THEN
                    SET NEW.consumo_intervalo_m3 = NEW.totalizador_m3 - prev_totalizador;
                    -- Proteger contra valores negativos (reset del medidor)
                    IF NEW.consumo_intervalo_m3 < 0 THEN
                        SET NEW.consumo_intervalo_m3 = 0;
                    END IF;
                ELSE
                    -- Primera lectura del medidor
                    SET NEW.consumo_intervalo_m3 = 0;
                END IF;
            END
        ");

        // Trigger 2: Detección de consumo brusco
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_insert_alerta_consumo_brusco;
            CREATE TRIGGER after_insert_alerta_consumo_brusco
            AFTER INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE avg_consumo DECIMAL(10,4);
                DECLARE total_lecturas INT;
                DECLARE umbral_brusco DECIMAL(10,4) DEFAULT 0.050;
                
                -- Calcular promedio de las últimas 24 horas (excluyendo la actual)
                SELECT COALESCE(AVG(consumo_intervalo_m3), 0), COUNT(*)
                INTO avg_consumo, total_lecturas
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 24 HOUR)
                AND fecha_hora < NEW.fecha_hora;
                
                -- Solo generar alerta si hay suficientes lecturas previas
                IF total_lecturas >= 10 AND NEW.consumo_intervalo_m3 > umbral_brusco THEN
                    -- Consumo brusco: más del triple del promedio O más de 200 litros
                    IF NEW.consumo_intervalo_m3 > (avg_consumo * 3) OR NEW.consumo_intervalo_m3 > 0.200 THEN
                        -- Evitar alertas duplicadas recientes
                        IF NOT EXISTS (
                            SELECT 1 FROM alerta
                            WHERE id_medidor = NEW.id_medidor
                            AND tipo_alerta = 'consumo_brusco'
                            AND estado = 'pendiente'
                            AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 1 HOUR)
                        ) THEN
                            INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                            VALUES (NEW.id_medidor, 'consumo_brusco', NEW.consumo_intervalo_m3, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                        END IF;
                    END IF;
                END IF;
            END
        ");

        // Trigger 3: Detección de fugas (basado en flow constante)
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_insert_alerta_fuga;
            CREATE TRIGGER after_insert_alerta_fuga
            AFTER INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE lecturas_continuas INT;
                DECLARE avg_flow DECIMAL(10,3);
                
                -- Contar lecturas con flow constante en las últimas 4 horas
                SELECT COUNT(*), COALESCE(AVG(flow_l_min), 0)
                INTO lecturas_continuas, avg_flow
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 4 HOUR)
                AND flow_l_min BETWEEN 0.5 AND 5.0;
                
                -- Si hay 16 lecturas continuas (4 horas) con flow constante
                IF lecturas_continuas >= 16 AND avg_flow BETWEEN 0.8 AND 3.0 THEN
                    -- Evitar alertas duplicadas
                    IF NOT EXISTS (
                        SELECT 1 FROM alerta
                        WHERE id_medidor = NEW.id_medidor
                        AND tipo_alerta = 'fuga'
                        AND estado = 'pendiente'
                        AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 6 HOUR)
                    ) THEN
                        INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                        VALUES (NEW.id_medidor, 'fuga', avg_flow, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                    END IF;
                END IF;
            END
        ");

        // Trigger 4: Consumo nocturno anómalo
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_insert_alerta_consumo_nocturno;
            CREATE TRIGGER after_insert_alerta_consumo_nocturno
            AFTER INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE hora_actual INT;
                
                SET hora_actual = HOUR(NEW.fecha_hora);
                
                -- Solo verificar entre 00:00 y 05:59 AM
                IF hora_actual BETWEEN 0 AND 5 THEN
                    -- Alerta 1: Consumo brusco nocturno (usa 'consumo_brusco')
                    IF NEW.consumo_intervalo_m3 > 0.050 THEN
                        -- Verificar si es un patrón sostenido
                        IF EXISTS (
                            SELECT 1 FROM consumo_agua 
                            WHERE id_medidor = NEW.id_medidor 
                            AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 30 MINUTE)
                            AND fecha_hora < NEW.fecha_hora
                            AND consumo_intervalo_m3 > 0.030
                        ) THEN
                            IF NOT EXISTS (
                                SELECT 1 FROM alerta
                                WHERE id_medidor = NEW.id_medidor
                                AND tipo_alerta = 'consumo_brusco'
                                AND estado = 'pendiente'
                                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 1 HOUR)
                            ) THEN
                                INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                                VALUES (NEW.id_medidor, 'consumo_brusco', NEW.consumo_intervalo_m3, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                            END IF;
                        END IF;
                    END IF;
                    
                    -- Alerta 2: Fuga nocturna (usa 'fuga_nocturna' que SÍ existe en tu ENUM)
                    IF NEW.flow_l_min BETWEEN 0.5 AND 3.0 THEN
                        -- Contar lecturas con flow constante en las últimas 2 horas nocturnas
                        IF (SELECT COUNT(*) 
                            FROM consumo_agua 
                            WHERE id_medidor = NEW.id_medidor
                            AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 2 HOUR)
                            AND HOUR(fecha_hora) BETWEEN 0 AND 5
                            AND flow_l_min BETWEEN 0.5 AND 3.0) >= 8 THEN
                            
                            IF NOT EXISTS (
                                SELECT 1 FROM alerta
                                WHERE id_medidor = NEW.id_medidor
                                AND tipo_alerta = 'fuga_nocturna'  -- ¡Ahora SÍ usamos fuga_nocturna!
                                AND estado = 'pendiente'
                                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 3 HOUR)
                            ) THEN
                                INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                                VALUES (NEW.id_medidor, 'fuga_nocturna', NEW.flow_l_min, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                            END IF;
                        END IF;
                    END IF;
                END IF;
            END
        ");
        // trigger 5: consumo excesivo
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_insert_alerta_consumo_excesivo;
            CREATE TRIGGER after_insert_alerta_consumo_excesivo
            AFTER INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE consumo_promedio_24h DECIMAL(10,4);
                DECLARE consumo_total_6h DECIMAL(10,4);
                DECLARE consumo_limite_diario DECIMAL(10,4) DEFAULT 2.000; -- 2000 litros/día
                DECLARE consumo_actual_6h DECIMAL(10,4);
                
                -- 1. Verificar si el consumo de las últimas 6 horas excede el límite diario
                SELECT COALESCE(SUM(consumo_intervalo_m3), 0)
                INTO consumo_actual_6h
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 6 HOUR);
                
                -- Si en 6 horas ya consumió más del límite diario normal
                IF consumo_actual_6h > consumo_limite_diario THEN
                    IF NOT EXISTS (
                        SELECT 1 FROM alerta
                        WHERE id_medidor = NEW.id_medidor
                        AND tipo_alerta = 'consumo_excesivo'
                        AND estado = 'pendiente'
                        AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 12 HOUR)
                    ) THEN
                        INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                        VALUES (NEW.id_medidor, 'consumo_excesivo', consumo_actual_6h, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                    END IF;
                END IF;
                
                -- 2. Verificar si el consumo actual es 3x superior al promedio histórico
                SELECT COALESCE(AVG(consumo_intervalo_m3), 0.015)
                INTO consumo_promedio_24h
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 24 HOUR)
                AND fecha_hora < NEW.fecha_hora;
                
                -- Si el consumo actual es 3 veces mayor al promedio y mayor a 100 litros
                IF NEW.consumo_intervalo_m3 > (consumo_promedio_24h * 3) AND NEW.consumo_intervalo_m3 > 0.100 THEN
                    -- Verificar patrón sostenido (más de 1 hora de consumo excesivo)
                    SELECT COALESCE(SUM(consumo_intervalo_m3), 0)
                    INTO consumo_total_6h
                    FROM consumo_agua
                    WHERE id_medidor = NEW.id_medidor
                    AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 1 HOUR)
                    AND consumo_intervalo_m3 > (consumo_promedio_24h * 2);
                    
                    IF consumo_total_6h > 0.300 THEN -- Más de 300 litros en 1 hora
                        IF NOT EXISTS (
                            SELECT 1 FROM alerta
                            WHERE id_medidor = NEW.id_medidor
                            AND tipo_alerta = 'consumo_excesivo'
                            AND estado = 'pendiente'
                            AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 3 HOUR)
                        ) THEN
                            INSERT INTO alerta (id_medidor, tipo_alerta, valor_detectado, fecha_hora, estado, created_at, updated_at)
                            VALUES (NEW.id_medidor, 'consumo_excesivo', NEW.consumo_intervalo_m3, NEW.fecha_hora, 'pendiente', NOW(), NOW());
                        END IF;
                    END IF;
                END IF;
            END
        ");
    }

    private function dropTriggers(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_calcular_consumo');
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_alerta_consumo_brusco');
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_alerta_fuga');
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_alerta_fuga_nocturna');
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_alerta_consumo_nocturno');
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_alerta_consumo_excesivo');
    }
};