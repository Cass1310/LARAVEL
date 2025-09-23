<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\FacturaEdificio;
use App\Models\FacturaDepartamento;
use App\Models\User;
use App\Models\Alerta;
use App\Models\Medidor;
use App\Models\Mantenimiento;
use App\Models\Departamento;
use App\Models\ConsumoAgua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class PropietarioController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $edificios = Edificio::with(['departamentos.medidores.consumos', 'departamentos.residentes'])
            ->where('id_propietario', $user->id)
            ->get();

        $metricas = $this->getMetricasPropietario($edificios);
        $consumoPorEdificio = $this->getConsumoPorEdificio($edificios);
        $facturasData = $this->getFacturasData($edificios);

        return view('propietario.dashboard', compact(
            'edificios', 
            'metricas', 
            'consumoPorEdificio',
            'facturasData'
        ));
    }

    // MÉTODOS PARA EDIFICIOS
    public function edificios()
    {
        $user = auth()->user();
        $edificios = Edificio::with(['departamentos.residentes', 'departamentos.medidores'])
            ->where('id_propietario', $user->id)
            ->get();

        return view('propietario.edificios', compact('edificios'));
    }

    public function edificioShow(Edificio $edificio)
    {
        Gate::authorize('view', $edificio);
        
        $edificio->load(['departamentos.residentes', 'departamentos.medidores.consumos']);
        
        return view('propietario.edificio-show', compact('edificio'));
    }

    // MÉTODOS PARA FACTURAS
    public function facturas()
    {
        $user = auth()->user();
        $facturas = FacturaEdificio::whereHas('edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })->with(['edificio', 'facturasDepartamento.departamento'])
          ->orderBy('fecha_emision', 'desc')
          ->get();

        return view('propietario.facturas.index', compact('facturas'));
    }

    public function crearFactura()
    {
        $user = auth()->user();
        $edificios = Edificio::where('id_propietario', $user->id)->get();
        
        return view('propietario.facturas.crear', compact('edificios'));
    }

    public function guardarFactura(Request $request)
    {
        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificio,id',
            'periodo' => 'required|string|max:20',
            'monto_total' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $edificio = Edificio::find($validated['id_edificio']);
        Gate::authorize('createFactura', $edificio);

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

        return redirect()->route('propietario.facturas')
            ->with('success', 'Factura creada exitosamente');
    }

    public function pagarFactura(FacturaEdificio $factura)
    {
        Gate::authorize('payFactura', $factura);

        $factura->update(['estado' => 'pagada']);

        return redirect()->back()->with('success', 'Factura marcada como pagada');
    }

    // MÉTODOS PARA RESIDENTES
    public function residentes()
    {
        $user = auth()->user();
        $residentes = User::whereHas('departamentosResidente.edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })->with(['departamentosResidente.edificio'])
          ->get();

        return view('propietario.residentes.index', compact('residentes'));
    }

    public function crearResidente()
    {
        $user = auth()->user();
        $edificios = Edificio::with('departamentos')
            ->where('id_propietario', $user->id)
            ->get();

        return view('propietario.residentes.crear', compact('edificios'));
    }

    public function guardarResidente(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'id_departamento' => 'required|exists:departamento,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date'
        ]);

        // Verificar que el departamento pertenezca al propietario
        $departamento = Departamento::find($validated['id_departamento']);
        Gate::authorize('manageResidentes', $departamento->edificio);

        // Crear usuario residente
        $residente = User::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'rol' => 'residente',
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion'],
            'created_by' => auth()->id()
        ]);

        // Asignar residente al departamento
        $residente->departamentosResidente()->attach($validated['id_departamento'], [
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin']
        ]);

        return redirect()->route('propietario.residentes')
            ->with('success', 'Residente creado y asignado exitosamente');
    }

    // MÉTODOS PARA ALERTAS
    public function alertas()
    {
        $user = auth()->user();
        $alertas = Alerta::whereHas('medidor.departamento.edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })->with(['medidor.departamento.edificio'])
          ->orderBy('fecha_hora', 'desc')
          ->paginate(10);

        return view('propietario.alertas', compact('alertas'));
    }

    public function resolverAlerta(Alerta $alerta)
    {
        Gate::authorize('update', $alerta->medidor->departamento->edificio);

        $alerta->update(['estado' => 'resuelta']);

        return redirect()->back()->with('success', 'Alerta marcada como resuelta');
    }

    // MÉTODOS PARA MANTENIMIENTOS
    public function mantenimientos()
    {
        $user = auth()->user();
        $mantenimientos = Mantenimiento::whereHas('medidor.departamento.edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })->with(['medidor.departamento.edificio'])
          ->orderBy('fecha', 'desc')
          ->paginate(10);

        return view('propietario.mantenimientos.index', compact('mantenimientos'));
    }

    public function crearMantenimiento()
    {
        $user = auth()->user();
        $edificios = Edificio::with(['departamentos.medidores'])
            ->where('id_propietario', $user->id)
            ->get();

        return view('propietario.mantenimientos.crear', compact('edificios'));
    }

    public function guardarMantenimiento(Request $request)
    {
        $validated = $request->validate([
            'id_medidor' => 'required|exists:medidor,id',
            'tipo' => 'required|in:preventivo,correctivo,instalacion,calibracion',
            'cobertura' => 'required|in:incluido_suscripcion,facturado',
            'costo' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:200'
        ]);

        // Verificar que el medidor pertenezca al propietario
        $medidor = Medidor::find($validated['id_medidor']);
        Gate::authorize('view', $medidor->departamento->edificio);

        Mantenimiento::create($validated);

        return redirect()->route('propietario.mantenimientos')
            ->with('success', 'Mantenimiento programado exitosamente');
    }

    // MÉTODOS PARA REPORTES
    public function reportes(Request $request)
    {
        $user = auth()->user();
        $edificios = Edificio::where('id_propietario', $user->id)->get();
        
        $edificioId = $request->input('edificio_id', null);
        $year = $request->input('year', now()->year);

        // Inicializar variables con valores por defecto
        $consumoData = [];
        $alertasData = [];
        $facturacionData = [];

        // Solo generar datos si hay un edificio seleccionado
        if ($edificioId || $request->has('year')) {
            $consumoData = $this->getReporteConsumo($user->id, $edificioId, $year);
            $alertasData = $this->getReporteAlertas($user->id, $edificioId, $year);
            $facturacionData = $this->getReporteFacturacion($user->id, $edificioId, $year);
        }

        return view('propietario.reportes', compact(
            'edificios',
            'consumoData',
            'alertasData',
            'facturacionData',
            'year',
            'edificioId'
        ));
    }

    private function getReporteConsumo($propietarioId, $edificioId, $year)
    {
        $query = ConsumoAgua::whereHas('medidor.departamento.edificio', function($query) use ($propietarioId) {
            $query->where('id_propietario', $propietarioId);
        });

        if ($edificioId) {
            $query->whereHas('medidor.departamento', function($query) use ($edificioId) {
                $query->where('id_edificio', $edificioId);
            });
        }

        $resultados = $query->selectRaw('MONTH(fecha_hora) as mes, SUM(volumen) as total')
            ->whereYear('fecha_hora', $year)
            ->groupBy('mes')
            ->get();

        // Inicializar array con 12 meses en cero
        $data = array_fill(1, 12, 0);
        
        foreach ($resultados as $resultado) {
            $data[$resultado->mes] = (float) $resultado->total;
        }

        return $data;
    }

    private function getReporteAlertas($propietarioId, $edificioId, $year)
    {
        $query = Alerta::whereHas('medidor.departamento.edificio', function($query) use ($propietarioId) {
            $query->where('id_propietario', $propietarioId);
        });

        if ($edificioId) {
            $query->whereHas('medidor.departamento', function($query) use ($edificioId) {
                $query->where('id_edificio', $edificioId);
            });
        }

        $resultados = $query->selectRaw('MONTH(fecha_hora) as mes, COUNT(*) as total')
            ->whereYear('fecha_hora', $year)
            ->groupBy('mes')
            ->get();

        // Inicializar array con 12 meses en cero
        $data = array_fill(1, 12, 0);
        
        foreach ($resultados as $resultado) {
            $data[$resultado->mes] = (int) $resultado->total;
        }

        return $data;
    }

    private function getReporteFacturacion($propietarioId, $edificioId, $year)
    {
        $query = FacturaEdificio::whereHas('edificio', function($query) use ($propietarioId) {
            $query->where('id_propietario', $propietarioId);
        });

        if ($edificioId) {
            $query->where('id_edificio', $edificioId);
        }

        $resultados = $query->selectRaw('MONTH(fecha_emision) as mes, SUM(monto_total) as total')
            ->whereYear('fecha_emision', $year)
            ->groupBy('mes')
            ->get();

        // Inicializar array con 12 meses en cero
        $data = array_fill(1, 12, 0);
        
        foreach ($resultados as $resultado) {
            $data[$resultado->mes] = (float) $resultado->total;
        }

        return $data;
    }
    // MÉTODOS PRIVADOS DE APOYO
    private function getMetricasPropietario($edificios)
    {
        $edificioIds = $edificios->pluck('id');

        return [
            'total_edificios' => $edificios->count(),
            'total_departamentos' => $edificios->sum(function($edificio) {
                return $edificio->departamentos->count();
            }),
            'total_residentes' => $edificios->sum(function($edificio) {
                return $edificio->departamentos->sum(function($departamento) {
                    return $departamento->residentes->count();
                });
            }),
            'alertas_pendientes' => Alerta::whereHas('medidor.departamento.edificio', function($query) use ($edificioIds) {
                $query->whereIn('id', $edificioIds);
            })->where('estado', 'pendiente')->count(),
            'facturas_pendientes' => FacturaEdificio::whereIn('id_edificio', $edificioIds)
                ->where('estado', 'pendiente')
                ->count()
        ];
    }
    private function getConsumoPorEdificio($edificios)
    {
        $data = [];
        foreach ($edificios as $edificio) {
            $consumo = $edificio->departamentos->sum(function($departamento) {
                return $departamento->medidores->sum(function($medidor) {
                    return $medidor->consumos()
                        ->whereMonth('fecha_hora', now()->month)
                        ->sum('volumen');
                });
            });
            
            $data[] = [
                'edificio' => $edificio->nombre,
                'consumo' => $consumo
            ];
        }
        return $data;
    }


    // // ACTUAL
    // private function getConsumoPorEdificio($edificios)
    // {
    //     $data = [];
    //     $anio = now()->year;
    //     $mes = now()->month;

    //     foreach ($edificios as $edificio) {
    //         $consumo = DB::selectOne('SELECT emprendimiento.fn_consumo_por_edificio(?, ?, ?) AS total', [
    //             $edificio->id,
    //             $anio,
    //             $mes
    //         ]);

    //         $data[] = [
    //             'edificio' => $edificio->nombre,
    //             'consumo' => (float) $consumo->total
    //         ];
    //     }

    //     return $data;
    // }
    private function getFacturasData($edificios)
    {
        $currentYear = now()->year;
        $data = [];

        foreach ($edificios as $edificio) {
            $facturas = FacturaEdificio::where('id_edificio', $edificio->id)
                ->whereYear('fecha_emision', $currentYear)
                ->get();

            $data[] = [
                'edificio' => $edificio->nombre,
                'facturas' => $facturas->pluck('monto_total')->toArray(),
                'meses' => $facturas->pluck('periodo')->toArray()
            ];
        }

        return $data;
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
    
}