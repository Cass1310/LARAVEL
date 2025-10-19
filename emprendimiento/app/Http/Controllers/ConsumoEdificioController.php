<?php

namespace App\Http\Controllers;

use App\Models\ConsumoEdificio;
use App\Models\Edificio;
use App\Models\ConsumoDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ConsumoEdificioController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', ConsumoEdificio::class);

        if (auth()->user()->rol === 'administrador') {
            $consumos = ConsumoEdificio::with(['edificio', 'creador'])->get();
        } else {
            $consumos = ConsumoEdificio::whereHas('edificio', function ($query) {
                $query->where('id_propietario', auth()->id());
            })->with(['edificio', 'creador'])->get();
        }

        return view('consumos-edificio.index', compact('consumos'));
    }

    public function create()
    {
        Gate::authorize('create', ConsumoEdificio::class);

        $edificios = Edificio::with('departamentos.medidores.consumos')->get();
        return view('consumos-edificio.create', compact('edificios'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ConsumoEdificio::class);

        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificio,id',
            'periodo' => 'required|string|max:20',
            'monto_total' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        // Verificar que no exista consumo para el mismo período
        $exists = ConsumoEdificio::where('id_edificio', $validated['id_edificio'])
            ->where('periodo', $validated['periodo'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['periodo' => 'Ya existe una consumo para este período']);
        }

        $validated['created_by'] = auth()->id();
        $consumo = ConsumoEdificio::create($validated);

        // Calcular y crear consumos por departamento
        $this->crearConsumosDepartamento($consumo);

        return redirect()->route('consumos-edificio.show', $consumo)
            ->with('success', 'Consumo creada exitosamente');
    }

    private function crearConsumosDepartamento(ConsumoEdificio $consumo)
    {
        $edificio = $consumo->edificio;
        $periodo = $consumo->periodo;
        
        foreach ($edificio->departamentos as $departamento) {
            $consumoTotal = $this->calcularConsumoDepartamento($departamento, $periodo);
            $porcentajeConsumo = $this->calcularPorcentajeConsumo($edificio, $departamento, $periodo);
            $montoAsignado = $consumo->monto_total * ($porcentajeConsumo / 100);

            ConsumoDepartamento::create([
                'id_consumo' => $consumo->id,
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

    public function show(ConsumoEdificio $consumoEdificio)
    {
        Gate::authorize('view', $consumoEdificio);

        $consumoEdificio->load(['edificio', 'consumosDepartamento.departamento.residentes']);
        
        return view('consumos-edificio.show', compact('consumoEdificio'));
    }

    public function markAsPaid(ConsumoEdificio $consumoEdificio)
    {
        Gate::authorize('pay', $consumoEdificio);

        $consumoEdificio->update(['estado' => 'pagada']);

        return redirect()->back()->with('success', 'Consumo marcada como pagada');
    }
}