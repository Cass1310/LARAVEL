<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Alerta;
use App\Models\Mantenimiento;
use App\Models\ConsumoAgua;
use App\Models\ConsumoDepartamento;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\ResidenteReporteExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ResidenteHistoricoExport;

class ResidenteController extends Controller
{
    public function departamento()
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->with(['edificio', 'medidores.gateway', 'medidores.consumos' => function($query) {
                $query->orderBy('fecha_hora', 'desc')->take(10);
            }])
            ->firstOrFail();

        return view('residente.departamento', compact('departamento'));
    }
    public function alertas(Request $request)
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        $query = Alerta::whereHas('medidor', function($query) use ($departamento) {
            $query->where('id_departamento', $departamento->id);
        })->with('medidor');

        // FILTROS
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_alerta', $request->tipo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $alertas = $query->orderBy('fecha_hora', 'desc')->paginate(10);

        return view('residente.alertas', compact('alertas', 'departamento'));
    }

    public function mantenimientos()
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        $mantenimientos = Mantenimiento::whereHas('medidor', function($query) use ($departamento) {
            $query->where('id_departamento', $departamento->id);
        })->with('medidor')
        ->orderBy('fecha', 'desc')
        ->get();

        return view('residente.mantenimientos', compact('mantenimientos', 'departamento'));
    }

    public function reportes(Request $request)
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        $year = $request->input('year', now()->year);

        // Datos para gráficos
        $consumoMensual = $this->getConsumoMensual($departamento, $year);
        $alertasMensual = $this->getAlertasMensual($departamento, $year);
        $pagosMensual = $this->getPagosMensual($departamento, $year);

        // Promedio solo sobre meses con pagos
        $mesesConPagos = count(array_filter($pagosMensual, fn($p) => $p > 0));
        $promedioMensual = $mesesConPagos > 0 ? array_sum($pagosMensual) / $mesesConPagos : 0;

        return view('residente.reportes', compact(
            'departamento', 
            'consumoMensual', 
            'alertasMensual', 
            'pagosMensual', 
            'promedioMensual',
            'year'
        ));
    }


    private function getConsumoMensual($departamento, $year)
    {
        $consumos = ConsumoAgua::whereHas('medidor', function($query) use ($departamento) {
            $query->where('id_departamento', $departamento->id);
        })
        ->selectRaw('MONTH(fecha_hora) as mes, SUM(volumen) as total')
        ->whereYear('fecha_hora', $year)
        ->groupBy('mes')
        ->get();

        $data = array_fill(0, 12, 0);
        foreach ($consumos as $consumo) {
            $data[$consumo->mes - 1] = (float) $consumo->total;
        }

        return $data;
    }


    // ACTUAL
    // private function getConsumoMensual($departamento, $year)
    // {
    //     $consumos = DB::table('vw_consumo_mensual_departamento')
    //         ->where('id_departamento', $departamento->id)
    //         ->where('anio', $year)
    //         ->get();

    //     $data = array_fill(0, 12, 0);

    //     foreach ($consumos as $consumo) {
    //         $data[$consumo->mes - 1] = (float) $consumo->total_consumo;
    //     }

    //     return $data;
    // }

    // TRANSACCION
    public function solicitarMantenimiento(Request $request)
    {
        $request->validate([
            'id_medidor' => 'required|exists:medidor,id',
            'tipo' => 'required|in:correctivo,calibracion,preventivo',
            'descripcion' => 'required|string|max:200',
        ]);
        $idMedidor = $request->input('id_medidor');
        $tipo = $request->input('tipo');
        $descripcion = $request->input('descripcion');
        $nuevoEstado = 'activo'; 

        $fecha = now()->addDays(7)->toDateString();
        if($tipo == 'preventivo'){
            $pago = 60;
        }else if($tipo == 'correctivo'){
            $pago = 100;
        }else{
            $pago = 80;
        }
        try {
            DB::statement('CALL registrar_mantenimiento(?, ?, ?, ?, ?, ?, ?)', [
                $idMedidor,
                $tipo,
                'cobrado',  
                $pago,             
                $fecha,
                $descripcion,
                $nuevoEstado
            ]);

            return back()->with('success', 'Mantenimiento registrado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar el mantenimiento: ' . $e->getMessage());
        }
    }

    private function getAlertasMensual($departamento, $year)
    {
        $alertas = Alerta::whereHas('medidor', function($query) use ($departamento) {
            $query->where('id_departamento', $departamento->id);
        })
        ->selectRaw('MONTH(fecha_hora) as mes, COUNT(*) as total')
        ->whereYear('fecha_hora', $year)
        ->groupBy('mes')
        ->get();

        $data = array_fill(0, 12, 0);
        foreach ($alertas as $alerta) {
            $data[$alerta->mes - 1] = (int) $alerta->total;
        }

        return $data;
    }

    private function getPagosMensual($departamento, $year)
    {
        $pagos = ConsumoDepartamento::where('id_departamento', $departamento->id)
            ->where('estado', 'pagado')
            ->whereYear('fecha_pago', $year)
            ->selectRaw('MONTH(fecha_pago) as mes, SUM(monto_asignado) as total')
            ->groupBy('mes')
            ->get();

        // Inicializamos los 12 meses con cero
        $data = array_fill(0, 12, 0);

        foreach ($pagos as $pago) {
            $data[$pago->mes - 1] = (float) $pago->total;
        }

        return $data;
    }


    // REPORTES EN PDF
    public function imprimirConsumo($id)
    {
        $user = auth()->user();
        $consumo = ConsumoDepartamento::where('id', $id)
            ->whereHas('departamento.residentes', function($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->with(['consumoEdificio.edificio', 'departamento'])
            ->firstOrFail();

        $pdf = PDF::loadView('residente.reportes.consumo-pdf', compact('consumo'));
        
        return $pdf->download('nota-consumo-' . $consumo->consumoEdificio->periodo . '.pdf');
    }
    public function exportarReportePdf(Request $request)
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        $year = $request->input('year', now()->year);

        $consumoMensual = $this->getConsumoMensual($departamento, $year);
        $alertasMensual = $this->getAlertasMensual($departamento, $year);
        $pagosMensual = $this->getPagosMensual($departamento, $year);
        $promedioMensual = count(array_filter($pagosMensual)) > 0 ? array_sum($pagosMensual) / count(array_filter($pagosMensual)) : 0;

        $pdf = Pdf::loadView('residente.reportes.reporte-completo-pdf', compact(
            'user','departamento', 'consumoMensual', 'alertasMensual', 'pagosMensual', 'year', 'promedioMensual'
        ));


        return $pdf->download('reporte-consumo-' . $year . '.pdf');
    }

    public function exportarReporteExcel(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', now()->year);
        
        return Excel::download(new ResidenteReporteExport($user, $year), 'reporte-consumo-' . $year . '.xlsx');
    }

    public function historicoConsumos()
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        // Últimos 5 meses
        $consumos = ConsumoDepartamento::where('id_departamento', $departamento->id)
            ->with('consumoEdificio')
            ->whereHas('consumoEdificio', function($query) {
                $query->where('fecha_emision', '>=', now()->subMonths(5));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('residente.reportes.historico-consumos', compact('consumos', 'departamento'));
    }

    public function exportarHistoricoExcel()
    {
        $user = auth()->user();
        
        return Excel::download(new ResidenteHistoricoExport($user), 'historico-consumos.xlsx');
    }
}