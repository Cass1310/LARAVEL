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

        // Crear los triggers
        DB::unprepared("
            -- Trigger 1: Calcular consumo_intervalo_m3 automáticamente
            DROP TRIGGER IF EXISTS before_insert_calcular_consumo;
            DELIMITER $$
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
            END$$
            DELIMITER ;

            -- Trigger 2: Detección de consumo brusco
            DROP TRIGGER IF EXISTS after_insert_alerta_consumo_brusco;
            DELIMITER $$
            CREATE TRIGGER after_insert_alerta_consumo_brusco
            AFTER INSERT ON consumo_agua
            FOR EACH ROW
            BEGIN
                DECLARE avg_consumo DECIMAL(10,4);
                DECLARE total_lecturas INT;
                DECLARE umbral_brusco DECIMAL(10,4) DEFAULT 0.050; -- 50 litros
                
                -- Calcular promedio de las últimas 24 horas (excluyendo la actual)
                SELECT COALESCE(AVG(consumo_intervalo_m3), 0), COUNT(*)
                INTO avg_consumo, total_lecturas
                FROM consumo_agua
                WHERE id_medidor = NEW.id_medidor
                  AND fecha_hora >= DATE_SUB(NEW.fecha_hora, INTERVAL 24 HOUR)
                  AND fecha_hora < NEW.fecha_hora;
                
                -- Solo generar alerta si hay suficientes lecturas previas y el consumo es significativo
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
            END$$
            DELIMITER ;

            -- Trigger 3: Detección de fugas (basado en flow constante)
            DROP TRIGGER IF EXISTS after_insert_alerta_fuga;
            DELIMITER $$
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
                  AND flow_l_min BETWEEN 0.5 AND 5.0; -- Flow constante típico de fuga
                
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
            END$$
            DELIMITER ;
        ");
    }

    public function down(): void
    {
        // Eliminar los triggers antes de eliminar la tabla
        DB::unprepared("
            DROP TRIGGER IF EXISTS before_insert_calcular_consumo;
            DROP TRIGGER IF EXISTS after_insert_alerta_consumo_brusco;
            DROP TRIGGER IF EXISTS after_insert_alerta_fuga;
        ");

        Schema::dropIfExists('consumo_agua');
    }
};