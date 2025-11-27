<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP VIEW IF EXISTS vw_consumo_detallado;
            CREATE VIEW vw_consumo_detallado AS
            SELECT 
                c.id,
                c.id_medidor,
                m.codigo_lorawan,
                m.device_eui,
                c.fecha_hora,
                c.totalizador_m3,
                c.consumo_intervalo_m3,
                c.flow_l_min,
                c.bateria,
                c.flags,
                c.tipo_registro,
                d.id AS id_departamento,
                d.numero_departamento,
                d.piso,
                e.id AS id_edificio,
                e.nombre AS nombre_edificio,
                e.direccion AS direccion_edificio,
                YEAR(c.fecha_hora) AS anio,
                MONTH(c.fecha_hora) AS mes,
                DAY(c.fecha_hora) AS dia,
                HOUR(c.fecha_hora) AS hora
            FROM
                consumo_agua c
                JOIN medidor m ON c.id_medidor = m.id
                JOIN departamento d ON m.id_departamento = d.id
                JOIN edificio e ON d.id_edificio = e.id
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS vw_consumo_detallado');
    }
};