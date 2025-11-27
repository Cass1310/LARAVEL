<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alerta;

class AlertaSeeder extends Seeder
{
    public function run(): void
    {
        $alertas = [
            [
                'id_medidor' => 1,
                'tipo_alerta' => 'consumo_brusco',
                'valor_detectado' => 15.02,
                'fecha_hora' => '2024-11-05 09:00:00',
                'estado' => 'resuelta',
            ],
            [
                'id_medidor' => 2,
                'tipo_alerta' => 'consumo_brusco',
                'valor_detectado' => 13.91,
                'fecha_hora' => '2024-11-05 09:00:00',
                'estado' => 'resuelta',
            ],
            // Agrega más alertas según sea necesario
        ];

        foreach ($alertas as $alerta) {
            Alerta::create($alerta);
        }
    }
}