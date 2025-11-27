<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS registrar_mantenimiento;
            CREATE PROCEDURE registrar_mantenimiento(
                IN p_id_departamento BIGINT,
                IN p_tipo ENUM('preventivo','correctivo','instalacion','calibracion'),
                IN p_cobertura ENUM('incluido_suscripcion','cobrado'),
                IN p_costo DECIMAL(12,2),
                IN p_fecha DATE,
                IN p_descripcion VARCHAR(200),
                IN p_nuevo_estado ENUM('activo','inactivo')
            )
            BEGIN
                DECLARE done INT DEFAULT 0;
                DECLARE med_id BIGINT;
                DECLARE med_cursor CURSOR FOR 
                    SELECT id FROM medidor WHERE id_departamento = p_id_departamento;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

                START TRANSACTION;

                OPEN med_cursor;

                read_loop: LOOP
                    FETCH med_cursor INTO med_id;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    -- Insertar mantenimiento para cada medidor
                    INSERT INTO mantenimiento(id_medidor, tipo, cobertura, costo, fecha, descripcion, created_at)
                    VALUES (med_id, p_tipo, p_cobertura, p_costo, p_fecha, p_descripcion, NOW());

                    -- Actualizar estado del medidor
                    UPDATE medidor
                    SET estado = p_nuevo_estado,
                        updated_at = NOW()
                    WHERE id = med_id;

                END LOOP;

                CLOSE med_cursor;

                COMMIT;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS registrar_mantenimiento');
    }
};