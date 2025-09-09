<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Departamento;
use App\Models\Edificio;
use App\Models\ConsumoAgua;
use App\Models\Alerta;
use App\Models\Medidor;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear un desarrollador
        User::factory()->create([
            'name' => 'Desarrollador',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'rol' => 'desarrollador',
        ]);

        // Crear 3 edificios
        Edificio::factory(3)->create()->each(function ($edificio) {
            // Por cada edificio, crear 5 departamentos
            $departamentos = Departamento::factory(5)->create([
                'edificio_id' => $edificio->id,
            ]);

            foreach ($departamentos as $dpto) {
                // Crear un residente por departamento
                $residente = User::factory()->create([
                    'rol' => 'residente'
                ]);
                $dpto->usuarios()->attach($residente->id);

                // Crear un medidor para ese departamento
                $medidor = Medidor::factory()->create([
                    'departamento_id' => $dpto->id
                ]);

                // Crear datos de consumo y alertas
                ConsumoAgua::factory(10)->create([
                    'medidor_id' => $medidor->id
                ]);

                Alerta::factory(1)->create([
                    'medidor_id' => $medidor->id
                ]);
            }

            // Crear un administrador para el edificio
            $admin = User::factory()->create([
                'rol' => 'administrador'
            ]);

            // Relacionarlo con todos los departamentos del edificio
            foreach ($departamentos as $dpto) {
                $dpto->usuarios()->attach($admin->id);
            }
        });
    }

}
