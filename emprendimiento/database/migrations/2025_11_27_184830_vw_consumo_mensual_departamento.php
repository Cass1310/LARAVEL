<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP VIEW IF EXISTS vw_consumo_mensual_departamento;
            CREATE VIEW vw_consumo_mensual_departamento AS
            SELECT 
                d.id AS id_departamento,
                YEAR(c.fecha_hora) AS anio,
                MONTH(c.fecha_hora) AS mes,
                SUM(c.consumo_intervalo_m3) AS total_consumo
            FROM
                consumo_agua c
                JOIN medidor m ON c.id_medidor = m.id
                JOIN departamento d ON m.id_departamento = d.id
            GROUP BY d.id, YEAR(c.fecha_hora), MONTH(c.fecha_hora)
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS vw_consumo_mensual_departamento');
    }
};