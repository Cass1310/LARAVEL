<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Alerta;
use App\Models\Mantenimiento;
use App\Models\ConsumoAgua;
use App\Models\FacturaDepartamento;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function alertas()
    {
        $user = auth()->user();
        $departamento = $user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                    ->orWhereNull('fecha_fin');
            })
            ->firstOrFail();

        $alertas = Alerta::whereHas('medidor', function($query) use ($departamento) {
            $query->where('id_departamento', $departamento->id);
        })->with('medidor')
        ->orderBy('fecha_hora', 'desc')
        ->paginate(10);

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

        return view('residente.reportes', compact(
            'departamento', 
            'consumoMensual', 
            'alertasMensual', 
            'pagosMensual', 
            'year'
        ));
    }


    // ANTERIOR A LA VISTA
    // private function getConsumoMensual($departamento, $year)
    // {
    //     $consumos = ConsumoAgua::whereHas('medidor', function($query) use ($departamento) {
    //         $query->where('id_departamento', $departamento->id);
    //     })
    //     ->selectRaw('MONTH(fecha_hora) as mes, SUM(volumen) as total')
    //     ->whereYear('fecha_hora', $year)
    //     ->groupBy('mes')
    //     ->get();

    //     $data = array_fill(0, 12, 0);
    //     foreach ($consumos as $consumo) {
    //         $data[$consumo->mes - 1] = (float) $consumo->total;
    //     }

    //     return $data;
    // }


    // ACTUAL
    private function getConsumoMensual($departamento, $year)
    {
        $consumos = DB::table('vw_consumo_mensual_departamento')
            ->where('id_departamento', $departamento->id)
            ->where('anio', $year)
            ->get();

        $data = array_fill(0, 12, 0);

        foreach ($consumos as $consumo) {
            $data[$consumo->mes - 1] = (float) $consumo->total_consumo;
        }

        return $data;
    }

    // TRANSACCION
    public function solicitarMantenimiento(Request $request)
    {
        $request->validate([
            'id_medidor' => 'required|exists:medidor,id',
            'tipo' => 'required|in:correctivo,calibracion',
            'descripcion' => 'required|string|max:200',
        ]);

        $idMedidor = $request->input('id_medidor');
        $tipo = $request->input('tipo');
        $descripcion = $request->input('descripcion');
        $nuevoEstado = 'activo'; 

        $fecha = now()->toDateString();

        try {
            DB::statement('CALL registrar_mantenimiento(?, ?, ?, ?, ?, ?, ?)', [
                $idMedidor,
                $tipo,
                'facturado',  
                0,             
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
        $pagos = FacturaDepartamento::where('id_departamento', $departamento->id)
            ->where('estado', 'pagado')
            ->whereYear('fecha_pago', $year)
            ->selectRaw('MONTH(fecha_pago) as mes, SUM(monto_asignado) as total')
            ->groupBy('mes')
            ->get();

        $data = array_fill(0, 12, 0);
        foreach ($pagos as $pago) {
            $data[$pago->mes - 1] = (float) $pago->total;
        }

        return $data;
    }
}