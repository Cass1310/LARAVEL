<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResidenteDepartamento;
use App\Models\User;
use App\Models\Departamento;

class ResidenteDepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $residentes = User::where('rol', 'residente')->get();
        $departamentos = Departamento::all();

        $asignaciones = [];
        
        for ($i = 0; $i < min(10, count($residentes), count($departamentos)); $i++) {
            $asignaciones[] = [
                'id_residente' => $residentes[$i]->id,
                'id_departamento' => $departamentos[$i]->id,
                'fecha_inicio' => '2024-10-01',
                'fecha_fin' => '2026-04-04',
            ];
        }

        foreach ($asignaciones as $asignacion) {
            ResidenteDepartamento::create($asignacion);
        }
    }
}