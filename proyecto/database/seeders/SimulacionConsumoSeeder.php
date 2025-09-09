<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumoAgua;
use App\Models\Medidor;
use Carbon\Carbon;

class SimulacionConsumoSeeder extends Seeder
{
    public function run(): void
    {
        $medidor = Medidor::first(); // o Medidor::find(1);

        if (!$medidor) {
            $this->command->warn("⚠️ No hay medidores en la base de datos.");
            return;
        }

        // 🔹 Simulación de fuga (ej. 1200L en 1h)
        $inicio = Carbon::now()->subMinutes(70);
        for ($i = 0; $i < 6; $i++) {
            ConsumoAgua::create([
                'medidor_id' => $medidor->id,
                'litros' => 200, // total 1200L
                'fecha_hora' => $inicio->addMinutes(10),
            ]);
        }

        // 🔹 Simulación de consumo prolongado (150L cada 30min por 3h)
        $inicio = Carbon::now()->subHours(3);
        for ($i = 0; $i < 6; $i++) {
            ConsumoAgua::create([
                'medidor_id' => $medidor->id,
                'litros' => 150,
                'fecha_hora' => $inicio->addMinutes(30),
            ]);
        }

        $this->command->info("✅ Simulaciones de consumo insertadas correctamente.");
    }
}
