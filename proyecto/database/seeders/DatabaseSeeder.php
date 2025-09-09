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
            'email' => 'linkayujrapoma@gmail.com',
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
                $fechasMensuales = [
                    '2024-09-01', '2024-10-01', '2024-11-01', '2024-12-01',
                    '2025-01-01', '2025-02-01', '2025-03-01', '2025-04-01', '2025-05-01',
                ];
                
                foreach ($fechasMensuales as $fecha) {
                    ConsumoAgua::factory()->create([
                        'medidor_id' => $medidor->id,
                        'fecha_hora' => $fecha,
                    ]);
                }

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
