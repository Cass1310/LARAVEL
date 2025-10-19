<?php

namespace App\Http\Controllers;

use App\Models\ConsumoDepartamento;
use App\Models\ConsumoAgua;
use App\Models\Alerta;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->rol === 'residente') {
            return $this->dashboardResidente($user);
        }
        
        // Aquí luego agregarás para otros roles
        return view('dashboard');
    }

    private function dashboardResidente($user)
    {
        // Obtener el departamento actual del residente
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->first();

        if (!$departamento) {
            return view('residente.dashboard', [
                'sin_departamento' => true,
                'user' => $user
            ]);
        }

        // Datos para la gráfica de consumo
        $consumoData = $this->getConsumoData($departamento);
        $consumoActual = $this->getConsumoActual($departamento);
        $metricas = $this->getMetricas($departamento);

        return view('residente.dashboard', [
            'departamento' => $departamento,
            'consumo' => $consumoActual,
            'consumoData' => $consumoData,
            'metricas' => $metricas,
            'sin_departamento' => false
        ]);
    }

    private function getConsumoData($departamento)
    {
        $currentMonth = now()->format('Y-m');
        $consumoDepartamento = $departamento->medidores->sum(function($medidor) use ($currentMonth) {
            return $medidor->consumos()
                ->whereYear('fecha_hora', substr($currentMonth, 0, 4))
                ->whereMonth('fecha_hora', substr($currentMonth, 5, 2))
                ->sum('volumen');
        });

        // Obtener consumo total del edificio
        $consumoTotalEdificio = $departamento->edificio->departamentos->sum(function($depto) use ($currentMonth) {
            return $depto->medidores->sum(function($medidor) use ($currentMonth) {
                return $medidor->consumos()
                    ->whereYear('fecha_hora', substr($currentMonth, 0, 4))
                    ->whereMonth('fecha_hora', substr($currentMonth, 5, 2))
                    ->sum('volumen');
            });
        });

        $porcentaje = $consumoTotalEdificio > 0 ? ($consumoDepartamento / $consumoTotalEdificio) * 100 : 0;

        return [
            'consumo_m3' => $consumoDepartamento,
            'porcentaje' => round($porcentaje, 2),
            'total_edificio' => $consumoTotalEdificio
        ];
    }

    private function getConsumoActual($departamento)
    {
        $currentMonth = now()->format('Y-m');
        
        return ConsumoDepartamento::where('id_departamento', $departamento->id)
            ->whereHas('consumoEdificio', function($query) use ($currentMonth) {
                $query->where('periodo', $currentMonth);
            })
            ->with('consumoEdificio')
            ->first();
    }

    private function getMetricas($departamento)
    {
        return [
            'alertas_pendientes' => Alerta::whereHas('medidor', function($query) use ($departamento) {
                $query->where('id_departamento', $departamento->id);
            })->where('estado', 'pendiente')->count(),
            
            'mantenimientos_pendientes' => Mantenimiento::whereHas('medidor', function($query) use ($departamento) {
                $query->where('id_departamento', $departamento->id);
            })->count(),
            
            'consumo_mes_actual' => $departamento->medidores->sum(function($medidor) {
                return $medidor->consumos()
                    ->whereMonth('fecha_hora', now()->month)
                    ->sum('volumen');
            })
        ];
    }
}