<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_consumo_por_edificio;
            CREATE FUNCTION fn_consumo_por_edificio(
                p_id_edificio BIGINT,
                p_anio INT,
                p_mes INT
            ) RETURNS DECIMAL(12,2)
            DETERMINISTIC
            BEGIN
                DECLARE total_consumo DECIMAL(12,2);

                SELECT SUM(c.consumo_intervalo_m3) INTO total_consumo
                FROM consumo_agua c
                INNER JOIN medidor m ON c.id_medidor = m.id
                INNER JOIN departamento d ON m.id_departamento = d.id
                WHERE d.id_edificio = p_id_edificio
                  AND YEAR(c.fecha_hora) = p_anio
                  AND MONTH(c.fecha_hora) = p_mes;

                RETURN IFNULL(total_consumo, 0);
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_consumo_por_edificio');
    }
};