<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumoDepartamento;
use App\Models\ConsumoEdificio;
use App\Models\Departamento;

class ConsumoDepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $consumosEdificio = ConsumoEdificio::all();

        foreach ($consumosEdificio as $consumoEdificio) {
            $departamentos = Departamento::where('id_edificio', $consumoEdificio->id_edificio)->get();
            
            foreach ($departamentos as $departamento) {
                ConsumoDepartamento::create([
                    'id_consumo' => $consumoEdificio->id,
                    'id_departamento' => $departamento->id,
                    'monto_asignado' => rand(50, 200),
                    'consumo_m3' => rand(10, 50),
                    'porcentaje_consumo' => rand(20, 40),
                    'estado' => 'pendiente',
                ]);
            }
        }
    }
}