<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumoAgua;
use App\Models\Medidor;
use Carbon\Carbon;

class ConsumoAguaSeeder extends Seeder
{
    public function run(): void
    {
        // Configuración de fechas (puedes modificar estos valores)
        $fechaInicio = Carbon::create(2025, 10, 1, 8, 0, 0); // Fecha de inicio
        $fechaFin = Carbon::create(2025, 11, 26, 12, 0, 0); // Fecha de fin
        $intervaloMinutos = 15; // Intervalo en minutos

        $medidores = Medidor::all();

        $consumos = [];

        // Generar consumos para cada medidor
        foreach ($medidores as $medidor) {
            $fechaActual = $fechaInicio->copy();

            while ($fechaActual <= $fechaFin) {
                // Generar un volumen aleatorio entre 1.0 y 5.0 m³
                $volumen = mt_rand(10, 50) / 10; // Valores entre 1.0 y 5.0

                $consumos[] = [
                    'id_medidor' => $medidor->id,
                    'fecha_hora' => $fechaActual->format('Y-m-d H:i:s'),
                    'volumen' => $volumen,
                    'tipo_registro' => 'transmision',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Avanzar el intervalo de tiempo
                $fechaActual->addMinutes($intervaloMinutos);

                // Para evitar memory issues, insertar cada 1000 registros
                if (count($consumos) >= 1000) {
                    ConsumoAgua::insert($consumos);
                    $consumos = [];
                }
            }
        }

        // Insertar los registros restantes
        if (!empty($consumos)) {
            ConsumoAgua::insert($consumos);
        }
    }
}