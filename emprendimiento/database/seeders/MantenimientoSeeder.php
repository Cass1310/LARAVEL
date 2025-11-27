<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mantenimiento;

class MantenimientoSeeder extends Seeder
{
    public function run(): void
    {
        $mantenimientos = [
            [
                'id_medidor' => 1,
                'tipo' => 'preventivo',
                'cobertura' => 'incluido_suscripcion',
                'costo' => 0.00,
                'fecha' => '2025-01-10',
                'descripcion' => 'preventivo servicio',
                'estado' => 'pendiente',
            ],
            [
                'id_medidor' => 1,
                'tipo' => 'correctivo',
                'cobertura' => 'cobrado',
                'costo' => 30.00,
                'fecha' => '2025-01-11',
                'descripcion' => 'correctivo servicio',
                'estado' => 'pendiente',
            ],
            // Agrega más mantenimientos según sea necesario
        ];

        foreach ($mantenimientos as $mantenimiento) {
            Mantenimiento::create($mantenimiento);
        }
    }
}