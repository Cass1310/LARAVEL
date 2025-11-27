<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumoEdificio;
use App\Models\Edificio;
use App\Models\User;

class ConsumoEdificioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'linkayujrapoma@gmail.com')->first();
        $edificios = Edificio::all();

        foreach ($edificios as $edificio) {
            ConsumoEdificio::create([
                'id_edificio' => $edificio->id,
                'periodo' => '2025-11',
                'monto_total' => rand(100, 500),
                'fecha_emision' => '2025-11-01',
                'fecha_vencimiento' => '2025-12-01',
                'estado' => 'pendiente',
                'created_by' => $admin->id,
            ]);
        }
    }
}