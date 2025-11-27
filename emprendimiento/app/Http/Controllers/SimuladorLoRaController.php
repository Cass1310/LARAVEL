<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsumoAgua;
use App\Models\Medidor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimuladorLoRaController extends Controller
{
    /**
     * Endpoint para simular recepción de datos LoRaWAN usando device_eui
     * POST /api/simular/lectura
     */
    public function simularLectura(Request $request)
    {
        $request->validate([
            'device_eui' => 'required_without:id_medidor|exists:medidor,device_eui',
            'id_medidor' => 'required_without:device_eui|exists:medidor,id',
            'totalizador_m3' => 'required|numeric|min:0',
            'flow_l_min' => 'nullable|numeric|min:0',
            'bateria' => 'nullable|integer|between:0,100',
            'fecha_hora' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            // Buscar medidor por device_eui o id_medidor
            if ($request->has('device_eui')) {
                $medidor = Medidor::where('device_eui', $request->device_eui)->first();
                $medidorId = $medidor->id;
            } else {
                $medidorId = $request->id_medidor;
            }

            $totalizador = $request->totalizador_m3;
            $fechaHora = $request->fecha_hora ? Carbon::parse($request->fecha_hora) : now();

            // Buscar última lectura para calcular el consumo del intervalo
            $ultimaLectura = ConsumoAgua::where('id_medidor', $medidorId)
                ->orderBy('fecha_hora', 'desc')
                ->first();

            $consumoIntervalo = 0;
            if ($ultimaLectura) {
                $consumoIntervalo = max(0, $totalizador - $ultimaLectura->totalizador_m3);
            }

            // Crear nueva lectura
            $lectura = ConsumoAgua::create([
                'id_medidor' => $medidorId,
                'fecha_hora' => $fechaHora,
                'totalizador_m3' => $totalizador,
                'flow_l_min' => $request->flow_l_min,
                'bateria' => $request->bateria ?? rand(70, 100),
                'flags' => $request->flags ?? ['leak' => false, 'backflow' => false, 'tamper' => false],
                'consumo_intervalo_m3' => $consumoIntervalo,
                'tipo_registro' => 'transmision'
            ]);

            DB::commit();

            Log::info("Lectura simulada creada", [
                'medidor_id' => $medidorId,
                'totalizador' => $totalizador,
                'consumo_intervalo' => $consumoIntervalo
            ]);

            return response()->json([
                'success' => true,
                'data' => $lectura,
                'consumo_calculado' => $consumoIntervalo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en simulación LoRaWAN: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error procesando lectura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar múltiples lecturas automáticamente para pruebas
     * POST /api/simular/lecturas-masivas
     */
    public function generarLecturasMasivas(Request $request)
    {
        $request->validate([
            'id_medidor' => 'required|exists:medidor,id',
            'cantidad' => 'required|integer|min:1|max:1000',
            'intervalo_minutos' => 'required|integer|min:1|max:1440',
            'fecha_inicio' => 'required|date',
        ]);

        $medidorId = $request->id_medidor;
        $cantidad = $request->cantidad;
        $intervalo = $request->intervalo_minutos;
        $fechaActual = Carbon::parse($request->fecha_inicio);

        // Obtener último totalizador o empezar desde 0
        $ultimaLectura = ConsumoAgua::where('id_medidor', $medidorId)
            ->orderBy('fecha_hora', 'desc')
            ->first();

        $totalizadorActual = $ultimaLectura ? $ultimaLectura->totalizador_m3 : 100.000; // Empezar desde ~100 m³

        $lecturasCreadas = [];

        for ($i = 0; $i < $cantidad; $i++) {
            // Simular consumo normal (0-20 litros por intervalo)
            $consumoIntervalo = mt_rand(0, 20) / 1000; // Convertir a m³
            
            // Ocasionalmente generar consumo alto para probar alertas (1% de probabilidad)
            if (mt_rand(1, 100) == 1) {
                $consumoIntervalo += mt_rand(50, 200) / 1000; // 50-200 litros extra
            }

            $totalizadorActual += $consumoIntervalo;
            $totalizadorActual = round($totalizadorActual, 3);

            // Calcular flow instantáneo (L/min) basado en consumo del intervalo
            $flowLMin = $consumoIntervalo > 0 ? 
                round(($consumoIntervalo / ($intervalo / 60)) * 1000, 3) : 0;

            $lectura = ConsumoAgua::create([
                'id_medidor' => $medidorId,
                'fecha_hora' => $fechaActual,
                'totalizador_m3' => $totalizadorActual,
                'flow_l_min' => $flowLMin,
                'bateria' => mt_rand(70, 100),
                'flags' => ['leak' => false, 'backflow' => false, 'tamper' => false],
                'consumo_intervalo_m3' => $consumoIntervalo,
                'tipo_registro' => 'transmision'
            ]);

            $lecturasCreadas[] = $lectura;
            $fechaActual->addMinutes($intervalo);
        }

        return response()->json([
            'success' => true,
            'lecturas_creadas' => count($lecturasCreadas),
            'primera_lectura' => $lecturasCreadas[0] ?? null,
            'ultima_lectura' => end($lecturasCreadas) ?: null
        ]);
    }
}