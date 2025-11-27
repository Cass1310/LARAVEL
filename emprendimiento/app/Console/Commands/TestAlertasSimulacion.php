<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestAlertasSimulacion extends Command
{
    protected $signature = 'test:alertas {medidor=6}';

    public function handle()
    {
        $medidorId = $this->argument('medidor');
        
        $this->info("=== INICIANDO PRUEBAS DE TODAS LAS ALERTAS PARA MEDIDOR {$medidorId} ===");
        
        $this->simularFugaProlongada($medidorId);           // Prueba: 'fuga'
        $this->simularConsumoBrusco($medidorId);            // Prueba: 'consumo_brusco'  
        $this->simularConsumoExcesivo($medidorId);          // Prueba: 'consumo_excesivo' (NUEVO)
        $this->simularFugaNocturna($medidorId);             // Prueba: 'fuga_nocturna' (NUEVO)
        $this->simularConsumoNocturnoAnomalo($medidorId);   // Prueba: 'consumo_brusco' nocturno
        
        $this->verificarAlertas($medidorId);
        
        return Command::SUCCESS;
    }

    private function simularFugaProlongada($medidorId)
    {
        $this->info("\n1. SIMULANDO FUGA PROLONGADA (tipo: 'fuga')...");
        
        $fechaBase = Carbon::now()->subHours(3);
        $totalizador = $this->getUltimoTotalizador($medidorId) ?? 200.000;

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
        $this->info("   ✓ Insertadas 20 lecturas de fuga constante (1.2 L/min)");
    }

    private function simularConsumoBrusco($medidorId)
    {
        $this->info("\n2. SIMULANDO CONSUMO BRUSCO (tipo: 'consumo_brusco')...");
        
        $fecha = Carbon::now()->subMinutes(45);
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
        
        $this->info("   ✓ Insertado consumo brusco de 350 litros en 15 minutos");
    }

    private function simularConsumoExcesivo($medidorId)
    {
        $this->info("\n3. SIMULANDO CONSUMO EXCESIVO (tipo: 'consumo_excesivo')...");
        
        $fechaBase = Carbon::now()->subHours(4); // Más realista para la lógica del trigger
        $totalizador = $this->getUltimoTotalizador($medidorId) ?? 250.000;

        // Simular consumo excesivo (80 litros cada 15 min = 320 L/hora = 7,680 L/día)
        $batch = [];
        for ($i = 0; $i < 16; $i++) { // 4 horas de consumo excesivo
            $fecha = $fechaBase->copy()->addMinutes($i * 15);
            $consumo = 0.080; // 80 litros cada 15 minutos
            
            $batch[] = [
                'id_medidor' => $medidorId,
                'fecha_hora' => $fecha,
                'totalizador_m3' => $totalizador += $consumo,
                'flow_l_min' => 5.333,
                'bateria' => 90,
                'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
                'consumo_intervalo_m3' => $consumo,
                'tipo_registro' => 'transmision',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('consumo_agua')->insert($batch);
        $this->info("   ✓ Insertadas 16 lecturas de consumo excesivo (80 L/15min = 7,680 L/día)");
        
        // Agregar un consumo individual muy alto para probar la segunda condición
        DB::table('consumo_agua')->insert([
            'id_medidor' => $medidorId,
            'fecha_hora' => $fechaBase->copy()->addHours(5),
            'totalizador_m3' => $totalizador + 0.150,
            'flow_l_min' => 10.000,
            'bateria' => 90,
            'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
            'consumo_intervalo_m3' => 0.150,
            'tipo_registro' => 'transmision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->info("   ✓ Insertado consumo individual alto (150L) para prueba adicional");
    }

    private function simularFugaNocturna($medidorId)
    {
        $this->info("\n4. SIMULANDO FUGA NOCTURNA (tipo: 'fuga_nocturna')...");
        
        // Usar horario nocturno (01:00 AM de hoy)
        $fechaBase = Carbon::today()->setHour(1)->setMinute(0);
        $totalizador = $this->getUltimoTotalizador($medidorId) ?? 300.000;

        // Simular fuga constante durante horario nocturno
        $batch = [];
        for ($i = 0; $i < 12; $i++) { // 3 horas de fuga nocturna
            $fecha = $fechaBase->copy()->addMinutes($i * 15);
            $consumo = 0.015; // Flow constante ~1.0 L/min
            
            $batch[] = [
                'id_medidor' => $medidorId,
                'fecha_hora' => $fecha,
                'totalizador_m3' => $totalizador += $consumo,
                'flow_l_min' => 1.0,
                'bateria' => 94,
                'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
                'consumo_intervalo_m3' => $consumo,
                'tipo_registro' => 'transmision',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('consumo_agua')->insert($batch);
        $this->info("   ✓ Insertadas 12 lecturas de fuga nocturna (1.0 L/min entre 01:00-04:00)");
    }

    private function simularConsumoNocturnoAnomalo($medidorId)
    {
        $this->info("\n5. SIMULANDO CONSUMO NOCTURNO ANÓMALO (tipo: 'consumo_brusco')...");
        
        $fechaNocturna = Carbon::today()->setHour(2)->setMinute(0);
        $totalizador = $this->getUltimoTotalizador($medidorId) ?? 350.000;

        // Consumos anómalos nocturnos
        DB::table('consumo_agua')->insert([
            'id_medidor' => $medidorId,
            'fecha_hora' => $fechaNocturna,
            'totalizador_m3' => $totalizador + 0.200,
            'flow_l_min' => 13.333,
            'bateria' => 95,
            'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
            'consumo_intervalo_m3' => 0.200,
            'tipo_registro' => 'transmision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('consumo_agua')->insert([
            'id_medidor' => $medidorId,
            'fecha_hora' => $fechaNocturna->copy()->addMinutes(15),
            'totalizador_m3' => $totalizador + 0.350,
            'flow_l_min' => 10.000,
            'bateria' => 95,
            'flags' => json_encode(['leak' => false, 'backflow' => false, 'tamper' => false]),
            'consumo_intervalo_m3' => 0.150,
            'tipo_registro' => 'transmision',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("   ✓ Insertados 2 consumos nocturnos anómalos (200L y 150L a las 2:00 AM)");
    }

    private function verificarAlertas($medidorId)
    {
        $alertas = DB::table('alerta')
            ->where('id_medidor', $medidorId)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info("\n=== RESULTADO: ALERTAS GENERADAS ===");
        
        if ($alertas->count() === 0) {
            $this->error("   ✗ NO se generaron alertas. Revisa los triggers.");
            return;
        }

        $tiposEncontrados = [];
        foreach ($alertas as $alerta) {
            $this->info("   ✓ Tipo: {$alerta->tipo_alerta} | Valor: {$alerta->valor_detectado} | Estado: {$alerta->estado} | Fecha: {$alerta->fecha_hora}");
            $tiposEncontrados[] = $alerta->tipo_alerta;
        }

        $this->info("\n=== RESUMEN ===");
        $this->info("Alertas generadas: " . $alertas->count());
        $this->info("Tipos encontrados: " . implode(', ', array_unique($tiposEncontrados)));
        
        $todosTipos = ['fuga', 'consumo_brusco', 'consumo_excesivo', 'fuga_nocturna'];
        $faltantes = array_diff($todosTipos, array_unique($tiposEncontrados));
        
        if (!empty($faltantes)) {
            $this->error("Tipos faltantes: " . implode(', ', $faltantes));
            $this->error("¡Revisa los triggers para estos tipos!");
        } else {
            $this->info("¡TODOS los tipos de alerta fueron generados correctamente! 🎉");
        }
    }

    private function getUltimoTotalizador($medidorId)
    {
        return DB::table('consumo_agua')
            ->where('id_medidor', $medidorId)
            ->orderBy('fecha_hora', 'desc')
            ->value('totalizador_m3');
    }
}