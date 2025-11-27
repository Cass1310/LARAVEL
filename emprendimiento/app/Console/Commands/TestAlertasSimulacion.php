<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestAlertasSimulacion extends Command
{
    protected $signature = 'test:alertas {medidor=6}';
    protected $description = 'Simular patrones para probar alertas de fugas y consumo brusco';

    public function handle()
    {
        $medidorId = $this->argument('medidor');
        
        $this->simularFugaProlongada($medidorId);
        $this->simularConsumoBrusco($medidorId);
        $this->verificarAlertas($medidorId);
        
        return Command::SUCCESS;
    }

    private function simularFugaProlongada($medidorId)
    {
        $this->info("Simulando fuga prolongada para medidor {$medidorId}...");
        
        $fechaBase = Carbon::now()->subHours(2);
        $totalizador = DB::table('consumo_agua')
            ->where('id_medidor', $medidorId)
            ->orderBy('fecha_hora', 'desc')
            ->value('totalizador_m3') ?? 200.000;

        $batch = [];
        for ($i = 0; $i < 20; $i++) {
            $fecha = $fechaBase->copy()->addMinutes($i * 15);
            $consumo = 0.018; // Flow constante ~1.2 L/min
            
            $batch[] = [
                'id_medidor' => $medidorId,
                'fecha_hora' => $fecha,
                'totalizador_m3' => $totalizador += $consumo,
                'flow_l_min' => 1.2,
                'bateria' => 92,
                'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
                'consumo_intervalo_m3' => $consumo,
                'tipo_registro' => 'transmision',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('consumo_agua')->insert($batch);
        $this->info("Insertadas 20 lecturas de fuga simulada");
    }

    private function simularConsumoBrusco($medidorId)
    {
        $this->info("Simulando consumo brusco para medidor {$medidorId}...");
        
        $fecha = Carbon::now()->subMinutes(30);
        $ultimo = DB::table('consumo_agua')
            ->where('id_medidor', $medidorId)
            ->orderBy('fecha_hora', 'desc')
            ->first();

        DB::table('consumo_agua')->insert([
            'id_medidor' => $medidorId,
            'fecha_hora' => $fecha,
            'totalizador_m3' => $ultimo->totalizador_m3 + 0.350,
            'flow_l_min' => 23.333,
            'bateria' => 88,
            'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
            'consumo_intervalo_m3' => 0.350,
            'tipo_registro' => 'transmision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info("Insertado consumo brusco de 350 litros");
    }

    private function verificarAlertas($medidorId)
    {
        $alertas = DB::table('alerta')
            ->where('id_medidor', $medidorId)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info("\n=== ALERTAS GENERADAS ===");
        foreach ($alertas as $alerta) {
            $this->info("Tipo: {$alerta->tipo_alerta} | Valor: {$alerta->valor_detectado} | Estado: {$alerta->estado}");
        }
    }
}