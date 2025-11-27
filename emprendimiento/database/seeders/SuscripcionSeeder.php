<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Suscripcion;
use App\Models\Cliente;

class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();

        $suscripciones = [
            [
                'tipo' => 'anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'id_cliente' => $clientes[0]->id,
            ],
            [
                'tipo' => 'anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'id_cliente' => $clientes[1]->id,
            ],
            [
                'tipo' => 'anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'id_cliente' => $clientes[2]->id,
            ],
            [
                'tipo' => 'anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'id_cliente' => $clientes[0]->id,
            ],
            [
                'tipo' => 'anual',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'id_cliente' => $clientes[1]->id,
            ],
        ];

        foreach ($suscripciones as $suscripcion) {
            Suscripcion::create($suscripcion);
        }
    }
}