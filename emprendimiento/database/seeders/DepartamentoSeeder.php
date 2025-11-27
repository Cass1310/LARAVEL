<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departamento;
use App\Models\Edificio;
use App\Models\User;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'linkayujrapoma@gmail.com')->first();
        $edificios = Edificio::all();

        $departamentos = [];
        
        foreach ($edificios as $edificio) {
            for ($i = 1; $i <= 3; $i++) {
                $departamentos[] = [
                    'id_edificio' => $edificio->id,
                    'numero_departamento' => $edificio->id . $i,
                    'piso' => $i,
                    'created_by' => $admin->id,
                ];
            }
        }

        foreach ($departamentos as $departamento) {
            Departamento::create($departamento);
        }
    }
}