<?php

namespace App\Http\Controllers;

use App\Models\ConsumoAgua;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ConsumoAguaController extends Controller
{
    public function index()
    {
        $consumos = ConsumoAgua::with('medidor')->get();
        return view('consumos.index', compact('consumos'));
    }

    public function show(ConsumoAgua $consumo)
    {
        $consumo->load('medidor');
        return view('consumos.show', compact('consumo'));
    }

    public function mensual(Request $request)
    {
        $edificioId = $request->input('edificio_id');
        $year = $request->input('year', now()->year); // valor predeterminado: año actual

        // Base query
        $query = DB::table('consumos_agua')
            ->join('medidores', 'consumos_agua.medidor_id', '=', 'medidores.id')
            ->join('departamentos', 'medidores.departamento_id', '=', 'departamentos.id')
            ->join('edificios', 'departamentos.edificio_id', '=', 'edificios.id')
            ->select(
                DB::raw('MONTH(consumos_agua.fecha_hora) as mes'),
                DB::raw('SUM(consumos_agua.litros) as total')
            )
            ->whereYear('consumos_agua.fecha_hora', $year); // Filtro por año

        if ($edificioId) {
            $query->where('edificios.id', $edificioId);
        }

        $resultados = $query->groupBy('mes')->orderBy('mes')->get();

        // Preparar datos para el gráfico
        $mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $meses = [];
        $litros = [];

        foreach ($resultados as $fila) {
            $meses[] = $mesesNombres[$fila->mes - 1];
            $litros[] = $fila->total;
        }

        $edificios = \App\Models\Edificio::all();

        return view('consumos.mensual', compact('meses', 'litros', 'edificios', 'year'));
    }

    public function comparativa(Request $request)
    {
        $tipo = $request->input('tipo', 'trimestral'); // por defecto

        $query = DB::table('consumos_agua')
            ->select(
                DB::raw($tipo === 'trimestral'
                    ? 'QUARTER(fecha_hora) as periodo'
                    : 'IF(MONTH(fecha_hora) BETWEEN 1 AND 6, 1, 2) as periodo'),
                DB::raw('YEAR(fecha_hora) as anio'),
                DB::raw('SUM(litros) as total')
            )
            ->groupBy('anio', 'periodo')
            ->orderBy('anio')
            ->orderBy('periodo')
            ->get();

        $etiquetas = [];
        $valores = [];

        foreach ($query as $fila) {
            $etiquetas[] = ($tipo === 'trimestral'
                ? "T{$fila->periodo} {$fila->anio}"
                : "S{$fila->periodo} {$fila->anio}");
            $valores[] = $fila->total;
        }

        return view('consumos.comparativa', compact('etiquetas', 'valores'));
    }

}
