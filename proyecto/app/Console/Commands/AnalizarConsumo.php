<?php

namespace App\Console\Commands;
use App\Models\Medidor;
use Carbon\Carbon;
use App\Models\ConsumoAgua;
use Illuminate\Console\Command;

class AnalizarConsumo extends Command
{
    protected $signature = 'consumo:analizar';

    protected $description = 'Analiza los patrones de consumo y genera alertas si es necesario.';

    public function handle()
    {
        $this->info('Análisis de consumo ejecutado.');

        $medidores = \App\Models\Medidor::all();

        foreach ($medidores as $medidor) {
            // 🔹 Verificar consumo excesivo en 1 hora
            $desde1h = now()->subHour();

            $litrosUltimaHora = \App\Models\ConsumoAgua::where('medidor_id', $medidor->id)
                ->where('fecha_hora', '>=', $desde1h)
                ->sum('litros');

            if ($litrosUltimaHora > 1000) {
                \App\Models\Alerta::create([
                    'medidor_id' => $medidor->id,
                    'tipo' => 'fuga',
                    'mensaje' => "Consumo excesivo detectado: {$litrosUltimaHora} litros en 1 hora.",
                    'resuelta' => false,
                ]);
            }

            // 🔹 Verificar consumo prolongado por más de 3 horas
            $desde3h = now()->subHours(3);

            $consumosContinuos = \App\Models\ConsumoAgua::where('medidor_id', $medidor->id)
                ->where('fecha_hora', '>=', $desde3h)
                ->orderBy('fecha_hora')
                ->get();

            if ($consumosContinuos->count() >= 6) {
                \App\Models\Alerta::create([
                    'medidor_id' => $medidor->id,
                    'tipo' => 'consumo_alto',
                    'mensaje' => "Consumo constante detectado por más de 3 horas.",
                    'resuelta' => false,
                ]);
            }
        }
    }

}
