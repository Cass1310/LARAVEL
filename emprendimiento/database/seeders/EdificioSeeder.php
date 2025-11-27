<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Edificio;
use App\Models\User;

class EdificioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'linkayujrapoma@gmail.com')->first();

        $edificios = [
            [
                'id_propietario' => User::where('email', 'juan@gmail.com')->first()->id,
                'nombre' => 'Edificio 1',
                'direccion' => 'Calle Falsa 1',
                'created_by' => $admin->id,
            ],
            [
                'id_propietario' => User::where('email', 'maria@gmail.com')->first()->id,
                'nombre' => 'Edificio 2',
                'direccion' => 'Calle Falsa 2',
                'created_by' => $admin->id,
            ],
            [
                'id_propietario' => User::where('email', 'carlos@gmail.com')->first()->id,
                'nombre' => 'Edificio 3',
                'direccion' => 'Calle Falsa 3',
                'created_by' => $admin->id,
            ],
            [
                'id_propietario' => User::where('email', 'juan@gmail.com')->first()->id,
                'nombre' => 'Edificio 4',
                'direccion' => 'Calle Falsa 4',
                'created_by' => $admin->id,
            ],
            [
                'id_propietario' => User::where('email', 'maria@gmail.com')->first()->id,
                'nombre' => 'Edificio 5',
                'direccion' => 'Calle Falsa 5',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($edificios as $edificio) {
            Edificio::create($edificio);
        }
    }
}