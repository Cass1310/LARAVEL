<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsumoAgua;
use App\Models\Medidor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsumoAguaSeeder extends Seeder
{
    public function run(): void
    {
        // Configuración
        $fechaInicio = Carbon::create(2025, 10, 1, 0, 0, 0);
        $fechaFin = Carbon::create(2025, 11, 26, 23, 45, 0);
        $intervaloMinutos = 15; // Cada 15 minutos como el manual sugiere

        $medidores = Medidor::all();

        foreach ($medidores as $medidor) {
            $this->command->info("Generando consumos para medidor {$medidor->id}...");

            // Iniciar totalizador en valor realista
            $totalizador = 100.000 + (mt_rand(0, 1000) / 1000); // 100-101 m³ inicial
            $fechaActual = $fechaInicio->copy();

            $batch = [];
            $contador = 0;

            while ($fechaActual <= $fechaFin) {
                // Patrón de consumo basado en hora del día
                $hora = $fechaActual->hour;
                $consumoBase = $this->calcularConsumoBase($hora);
                
                // Variación aleatoria
                $variacion = mt_rand(-20, 20) / 1000; // ±20 litros
                $consumoIntervalo = max(0, $consumoBase + $variacion);

                // Ocasionalmente generar consumo alto (para probar alertas)
                if (mt_rand(1, 500) == 1) { // 0.2% de probabilidad
                    $consumoIntervalo += mt_rand(100, 500) / 1000; // 100-500 litros extra
                }

                // Actualizar totalizador
                $totalizador += $consumoIntervalo;
                $totalizador = round($totalizador, 3);

                // Calcular flow instantáneo (L/min)
                $flowLMin = $consumoIntervalo > 0 ? 
                    round(($consumoIntervalo / ($intervaloMinutos / 60)) * 1000, 3) : 0;

                $batch[] = [
                    'id_medidor' => $medidor->id,
                    'fecha_hora' => $fechaActual->format('Y-m-d H:i:s'),
                    'totalizador_m3' => $totalizador,
                    'flow_l_min' => $flowLMin,
                    'bateria' => mt_rand(85, 100), // Batería alta normalmente
                    'flags' => json_encode([
                        'leak' => false,
                        'backflow' => false, 
                        'tamper' => false
                    ]),
                    'consumo_intervalo_m3' => $consumoIntervalo,
                    'tipo_registro' => 'transmision',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insertar en lotes para mejor performance
                if (count($batch) >= 500) {
                    DB::table('consumo_agua')->insert($batch);
                    $batch = [];
                    $this->command->info("  Insertadas 500 lecturas...");
                }

                $fechaActual->addMinutes($intervaloMinutos);
                $contador++;
            }

            // Insertar resto del batch
            if (!empty($batch)) {
                DB::table('consumo_agua')->insert($batch);
            }

            $this->command->info("  Total: {$contador} lecturas para medidor {$medidor->id}");
        }

        $this->command->info("Seeder de consumo_agua completado.");
    }

    /**
     * Calcular consumo base según hora del día (patrón realista)
     */
    private function calcularConsumoBase(int $hora): float
    {
        // Patrón típico de consumo doméstico
        return match(true) {
            // Madrugada: consumo muy bajo
            $hora >= 0 && $hora < 6 => mt_rand(0, 5) / 1000, // 0-5 litros/15min
            
            // Mañana: consumo medio (preparación para el día)
            $hora >= 6 && $hora < 9 => mt_rand(5, 15) / 1000, // 5-15 litros/15min
            
            // Medio día: consumo bajo
            $hora >= 9 && $hora < 12 => mt_rand(2, 8) / 1000, // 2-8 litros/15min
            
            // Tarde: consumo medio-alto
            $hora >= 12 && $hora < 18 => mt_rand(8, 20) / 1000, // 8-20 litros/15min
            
            // Noche: consumo alto (cena, limpieza, etc.)
            $hora >= 18 && $hora < 22 => mt_rand(10, 25) / 1000, // 10-25 litros/15min
            
            // Fin de noche: consumo bajo
            default => mt_rand(2, 10) / 1000, // 2-10 litros/15min
        };
    }
}