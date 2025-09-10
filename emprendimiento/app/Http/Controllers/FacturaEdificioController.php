<?php

namespace App\Http\Controllers;

use App\Models\FacturaEdificio;
use App\Models\Edificio;
use App\Models\FacturaDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class FacturaEdificioController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', FacturaEdificio::class);

        if (auth()->user()->rol === 'administrador') {
            $facturas = FacturaEdificio::with(['edificio', 'creador'])->get();
        } else {
            $facturas = FacturaEdificio::whereHas('edificio', function ($query) {
                $query->where('id_propietario', auth()->id());
            })->with(['edificio', 'creador'])->get();
        }

        return view('facturas-edificio.index', compact('facturas'));
    }

    public function create()
    {
        Gate::authorize('create', FacturaEdificio::class);

        $edificios = Edificio::with('departamentos.medidores.consumos')->get();
        return view('facturas-edificio.create', compact('edificios'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', FacturaEdificio::class);

        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificio,id',
            'periodo' => 'required|string|max:20',
            'monto_total' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        // Verificar que no exista factura para el mismo período
        $exists = FacturaEdificio::where('id_edificio', $validated['id_edificio'])
            ->where('periodo', $validated['periodo'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['periodo' => 'Ya existe una factura para este período']);
        }

        $validated['created_by'] = auth()->id();
        $factura = FacturaEdificio::create($validated);

        // Calcular y crear facturas por departamento
        $this->crearFacturasDepartamento($factura);

        return redirect()->route('facturas-edificio.show', $factura)
            ->with('success', 'Factura creada exitosamente');
    }

    private function crearFacturasDepartamento(FacturaEdificio $factura)
    {
        $edificio = $factura->edificio;
        $periodo = $factura->periodo;
        
        foreach ($edificio->departamentos as $departamento) {
            $consumoTotal = $this->calcularConsumoDepartamento($departamento, $periodo);
            $porcentajeConsumo = $this->calcularPorcentajeConsumo($edificio, $departamento, $periodo);
            $montoAsignado = $factura->monto_total * ($porcentajeConsumo / 100);

            FacturaDepartamento::create([
                'id_factura' => $factura->id,
                'id_departamento' => $departamento->id,
                'monto_asignado' => $montoAsignado,
                'consumo_m3' => $consumoTotal,
                'porcentaje_consumo' => $porcentajeConsumo,
            ]);
        }
    }

    private function calcularConsumoDepartamento($departamento, $periodo)
    {
        // Implementar lógica de cálculo de consumo basado en los medidores del departamento
        return $departamento->medidores->sum(function ($medidor) use ($periodo) {
            return $medidor->consumos()
                ->whereYear('fecha_hora', substr($periodo, 0, 4))
                ->whereMonth('fecha_hora', substr($periodo, 5, 2))
                ->sum('volumen');
        });
    }

    private function calcularPorcentajeConsumo($edificio, $departamento, $periodo)
    {
        $consumoDepartamento = $this->calcularConsumoDepartamento($departamento, $periodo);
        $consumoTotalEdificio = $edificio->departamentos->sum(function ($depto) use ($periodo) {
            return $this->calcularConsumoDepartamento($depto, $periodo);
        });

        return $consumoTotalEdificio > 0 ? ($consumoDepartamento / $consumoTotalEdificio) * 100 : 0;
    }

    public function show(FacturaEdificio $facturaEdificio)
    {
        Gate::authorize('view', $facturaEdificio);

        $facturaEdificio->load(['edificio', 'facturasDepartamento.departamento.residentes']);
        
        return view('facturas-edificio.show', compact('facturaEdificio'));
    }

    public function markAsPaid(FacturaEdificio $facturaEdificio)
    {
        Gate::authorize('pay', $facturaEdificio);

        $facturaEdificio->update(['estado' => 'pagada']);

        return redirect()->back()->with('success', 'Factura marcada como pagada');
    }
}