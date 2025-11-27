<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medidor;
use App\Models\Departamento;
use App\Models\Gateway;
use App\Models\User;

class MedidorSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'linkayujrapoma@gmail.com')->first();
        $gateway = Gateway::first();
        $departamentos = Departamento::all();

        $medidores = [];
        $counter = 1;

        foreach ($departamentos as $departamento) {
            $medidores[] = [
                'codigo_lorawan' => 'LW-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                'id_departamento' => $departamento->id,
                'id_gateway' => $gateway->id,
                'estado' => 'activo',
                'fecha_instalacion' => '2025-01-15',
                'created_by' => $admin->id,
            ];
            $counter++;
        }
        foreach ($medidores as $medidor) {
            Medidor::create($medidor);
        }
    }
}