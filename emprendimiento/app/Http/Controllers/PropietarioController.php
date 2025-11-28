<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\ConsumoEdificio;
use App\Models\ConsumoDepartamento;
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
use App\Exports\PropietarioReporteExport;
use App\Exports\PropietarioReporteDetalladoExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\NotasConsumoExport;
use App\Exports\TodasNotasConsumoExport;

class PropietarioController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $edificios = Edificio::with([
            'departamentos.residentes',
            'departamentos.medidores' => function($medidoresQuery) {
                $medidoresQuery->with(['consumos' => function($consumosQuery) {
                    $consumosQuery->orderBy('fecha_hora', 'desc')
                                ->limit(100); // Solo los últimos 100 registros
                }]);
            }
        ])
        ->where('id_propietario', $user->id)
        ->get();

        $metricas = $this->getMetricasPropietario($edificios);
        $consumoPorEdificio = $this->getConsumoPorEdificio($edificios);
        $consumosData = $this->getConsumosData1($edificios);

        return view('propietario.dashboard', compact(
            'edificios', 
            'metricas', 
            'consumoPorEdificio',
            'consumosData'
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

    public function edificioShowNOSIRVE(Edificio $edificio)
    {
        Gate::authorize('view', $edificio);
        
        $edificio->load(['departamentos.residentes', 'departamentos.medidores.consumos']);
        
        return view('propietario.edificio-show', compact('edificio'));
    }

    // MÉTODOS PARA consumoS
    public function consumos()
    {
        $user = auth()->user();
        $consumos = ConsumoEdificio::whereHas('edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })->with(['edificio', 'consumosDepartamento.departamento'])
          ->orderBy('fecha_emision', 'desc')
          ->get();

        return view('propietario.consumos.index', compact('consumos'));
    }

    public function crearConsumo()
    {
        $user = auth()->user();
        $edificios = Edificio::where('id_propietario', $user->id)->get();
        
        return view('propietario.consumos.crear', compact('edificios'));
    }

    public function guardarConsumo(Request $request)
    {
        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificio,id',
            'periodo' => 'required|string|max:20',
            'monto_total' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $edificio = Edificio::find($validated['id_edificio']);
        
        // Usar el gate correcto
        Gate::authorize('create-consumo', $edificio);

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

        return redirect()->route('propietario.consumos')
            ->with('success', 'Consumo creada exitosamente');
    }

    public function pagarConsumo(ConsumoEdificio $consumo)
    {
        Gate::authorize('payConsumo', $consumo);

        $consumo->update(['estado' => 'pagada']);

        return redirect()->back()->with('success', 'Consumo marcada como pagada');
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
        
        // Usar el gate correcto para gestionar residentes
        Gate::authorize('manage-residentes', $departamento->edificio);

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
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:200'
        ]);

        // Se fuerza cobertura a "cobrado"
        $validated['cobertura'] = 'cobrado';

        // Calcular costo según tipo
        if ($validated['tipo'] == 'preventivo') {
            $validated['costo'] = 60;
        } elseif ($validated['tipo'] == 'correctivo') {
            $validated['costo'] = 100;
        } else {
            $validated['costo'] = 80;
        }

        // Verificar permiso propietario-medidor
        $medidor = Medidor::find($validated['id_medidor']);
        Gate::authorize('view', $medidor->departamento->edificio);

        Mantenimiento::create($validated);

        return redirect()->route('propietario.mantenimientos')
            ->with('success', 'Mantenimiento registrado correctamente.');
    }


    // MÉTODOS ADICIONALES PARA MANTENIMIENTOS
    public function editarMantenimiento(Mantenimiento $mantenimiento)
    {
        Gate::authorize('update', $mantenimiento->medidor->departamento->edificio);
        
        return view('propietario.mantenimientos.editar', compact('mantenimiento'));
    }

    public function actualizarMantenimiento(Request $request, Mantenimiento $mantenimiento)
    {
        Gate::authorize('update', $mantenimiento->medidor->departamento->edificio);

        $validated = $request->validate([
            'tipo' => 'required|in:preventivo,correctivo,instalacion,calibracion',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:200'
        ]);

        // Forzar cobertura a "cobrado"
        $validated['cobertura'] = 'cobrado';

        // Calcular costo según tipo
        if ($validated['tipo'] == 'preventivo') {
            $validated['costo'] = 60;
        } elseif ($validated['tipo'] == 'correctivo') {
            $validated['costo'] = 100;
        } else {
            $validated['costo'] = 80;
        }

        $mantenimiento->update($validated);

        return redirect()->route('propietario.mantenimientos')
            ->with('success', 'Mantenimiento actualizado correctamente.');
    }



    public function eliminarMantenimiento(Mantenimiento $mantenimiento)
    {
        Gate::authorize('update', $mantenimiento->medidor->departamento->edificio);

        $mantenimiento->delete();

        return redirect()->route('propietario.mantenimientos')
            ->with('success', 'Mantenimiento eliminado exitosamente');
    }

    // MÉTODOS PARA CARGAR DATOS DINÁMICOS
    public function departamentosPorEdificio(Edificio $edificio)
    {
        Gate::authorize('view', $edificio);
        
        $departamentos = $edificio->departamentos()
            ->select('id', 'numero_departamento', 'piso')
            ->get();
        
        return response()->json($departamentos);
    }

    public function medidoresPorDepartamento(Departamento $departamento)
    {
        Gate::authorize('view', $departamento->edificio);
        
        $medidores = $departamento->medidores()
            ->where('estado', 'activo')
            ->select('id', 'codigo_lorawan', 'estado')
            ->get();
        
        return response()->json($medidores);
    }

    // MÉTODOS PARA REPORTES
    // public function reportes(Request $request)
    // {
    //     $user = auth()->user();
    //     $edificios = Edificio::where('id_propietario', $user->id)->get();
        
    //     $edificioId = $request->input('edificio_id', null);
    //     $year = $request->input('year', now()->year);

    //     // Inicializar variables con valores por defecto
    //     $consumoData = [];
    //     $alertasData = [];
    //     $consumosData = [];

    //     // Solo generar datos si hay un edificio seleccionado
    //     if ($edificioId || $request->has('year')) {
    //         $consumoData = $this->getReporteConsumo($user->id, $edificioId, $year);
    //         $alertasData = $this->getReporteAlertas($user->id, $edificioId, $year);
    //         $consumosData = $this->getReporteConsumos($user->id, $edificioId, $year);
    //     }

    //     return view('propietario.reportes', compact(
    //         'edificios',
    //         'consumoData',
    //         'alertasData',
    //         'consumosData',
    //         'year',
    //         'edificioId'
    //     ));
    // }

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

        $resultados = $query->selectRaw('MONTH(fecha_hora) as mes, SUM(consumo_intervalo_m3) as total') // CAMBIO
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

    private function getReporteConsumos($propietarioId, $edificioId, $year)
    {
        $query = ConsumoEdificio::whereHas('edificio', function($query) use ($propietarioId) {
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
            'consumos_pendientes' => ConsumoEdificio::whereIn('id_edificio', $edificioIds)
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
                        ->sum('consumo_intervalo_m3');
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
    private function getConsumosData1($edificios)
    {
        $currentYear = now()->year;
        $data = [];

        foreach ($edificios as $edificio) {
            $consumos = ConsumoEdificio::where('id_edificio', $edificio->id)
                ->whereYear('fecha_emision', $currentYear)
                ->get();

            $data[] = [
                'edificio' => $edificio->nombre,
                'consumos' => $consumos->pluck('monto_total')->toArray(),
                'meses' => $consumos->pluck('periodo')->toArray()
            ];
        }

        return $data;
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
        return $departamento->medidores->sum(function ($medidor) use ($periodo) {
            return $medidor->consumos()
                ->whereYear('fecha_hora', substr($periodo, 0, 4))
                ->whereMonth('fecha_hora', substr($periodo, 5, 2))
                // CAMBIO: usar consumo_intervalo_m3 en lugar de volumen
                ->sum('consumo_intervalo_m3');
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
    // peticion del lic
    public function edificioShow($id)
    {
        $user = auth()->user();
        $edificio = Edificio::with(['departamentos.residentes', 'departamentos.medidores.consumos'])
            ->where('id_propietario', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // Obtener datos para la gráfica de consumo por residente
        $consumoPorResidente = $this->getConsumoPorResidente($edificio);
        
        // Obtener consumos pendientes por residente
        $consumosResidentes = $this->getconsumosPendientesResidentes($edificio);

        return view('propietario.edificio-show', compact(
            'edificio',
            'consumoPorResidente',
            'consumosResidentes'
        ));
    }

    private function getConsumoPorResidente($edificio)
    {
        $currentMonth = now()->format('Y-m');
        $data = [];

        foreach ($edificio->departamentos as $departamento) {
            // Calcular consumo total del departamento
            $consumo = $departamento->medidores->sum(function($medidor) use ($currentMonth) {
                return $medidor->consumos()
                    ->whereYear('fecha_hora', substr($currentMonth, 0, 4))
                    ->whereMonth('fecha_hora', substr($currentMonth, 5, 2))
                    // CAMBIO: usar consumo_intervalo_m3 en lugar de volumen
                    ->sum('consumo_intervalo_m3');
            });

            if ($consumo > 0) {
                // ... resto del código igual
                $residentes = $departamento->residentes;
                $residentesNombres = '';
                
                if ($residentes->count() > 0) {
                    if ($residentes->count() <= 2) {
                        $residentesNombres = $residentes->pluck('nombre')->implode(', ');
                    } else {
                        $primerResidente = $residentes->first()->nombre;
                        $cantidadRestantes = $residentes->count() - 1;
                        $residentesNombres = $primerResidente . " y " . $cantidadRestantes . " más";
                    }
                }

                $data[] = [
                    'residente' => $residentesNombres,
                    'departamento' => $departamento->numero_departamento,
                    'consumo' => $consumo,
                    'total_residentes' => $residentes->count()
                ];
            }
        }

        return $data;
    }

    private function getconsumosPendientesResidentes($edificio)
    {
        $currentMonth = now()->format('Y-m');
        
        return ConsumoDepartamento::whereHas('consumoEdificio', function($query) use ($edificio, $currentMonth) {
                $query->where('id_edificio', $edificio->id)
                    ->where('periodo', $currentMonth);
            })
            ->whereHas('departamento.residentes')
            ->with(['departamento.residentes', 'consumoEdificio'])
            ->get()
            ->map(function($consumoDepto) {
                // Obtener todos los residentes del departamento separados por coma
                $residentesNombres = $consumoDepto->departamento->residentes
                    ->pluck('nombre')
                    ->implode(', ');

                return [
                    'residente' => $residentesNombres,
                    'departamento' => $consumoDepto->departamento->numero_departamento,
                    'monto_asignado' => $consumoDepto->monto_asignado,
                    'consumo_m3' => $consumoDepto->consumo_m3,
                    'porcentaje_consumo' => $consumoDepto->porcentaje_consumo,
                    'estado' => $consumoDepto->estado,
                    'fecha_vencimiento' => $consumoDepto->consumoEdificio->fecha_vencimiento
                ];
            });
    }
    // paga la factura edificio y las demas
    public function pagos()
    {
        $user = auth()->user();
        
        $consumosPendientes = ConsumoEdificio::whereHas('edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })
        ->with(['edificio', 'consumosDepartamento.departamento.residentes'])
        ->where('estado', 'pendiente')
        ->orderBy('fecha_vencimiento', 'asc')
        ->get();

        $consumosPagados = ConsumoEdificio::whereHas('edificio', function($query) use ($user) {
            $query->where('id_propietario', $user->id);
        })
        ->with(['edificio'])
        ->where('estado', 'pagada')
        ->orderBy('fecha_emision', 'desc')
        ->get();

        return view('propietario.pagos.index', compact('consumosPendientes', 'consumosPagados'));
    }

    public function mostrarPago(ConsumoEdificio $consumo)
    {
        Gate::authorize('pay-consumo', $consumo);
        $consumo->load(['edificio', 'consumosDepartamento.departamento.residentes']);
        return view('propietario.pagos.pagar', compact('consumo'));
    }

    public function procesarPago(Request $request, ConsumoEdificio $consumo)
    {
        Gate::authorize('pay-consumo', $consumo);

        $validated = $request->validate([
            'metodo_pago' => 'required|in:transferencia,deposito,efectivo,tarjeta',
            'comprobante' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'numero_comprobante' => 'nullable|string|max:50',
            'fecha_pago' => 'required|date'
        ]);

        // Procesar el pago
        DB::transaction(function () use ($consumo, $validated,$request) {
            // 1. Marcar consumo del edificio como pagado
            $consumo->update([
                'estado' => 'pagada',
                'fecha_pago' => $validated['fecha_pago']
            ]);

            // 2. Marcar todos los consumos de departamento como pagados
            $consumo->consumosDepartamento()->update([
                'estado' => 'pagado',
                'fecha_pago' => $validated['fecha_pago']
            ]);

            // 3. Guardar comprobante si existe
            if ($request->hasFile('comprobante')) {
                $comprobantePath = $request->file('comprobante')->store('comprobantes', 'public');
            }
        });

        return redirect()->route('propietario.pagos.index')
            ->with('success', 'Pago procesado exitosamente');
    }

    public function detallePago(ConsumoEdificio $consumo)
    {
         Gate::authorize('view', $consumo);
        
        $consumo->load([
            'edificio', 
            'consumosDepartamento.departamento.residentes',
            'consumosDepartamento' => function($query) {
                $query->orderBy('id_departamento');
            }
        ]);

        return view('propietario.pagos.detalle', compact('consumo'));
    }
    //REPORTES EN PDF Y EXCEL.
    public function reportes(Request $request)
    {
        $user = auth()->user();
        $edificios = Edificio::where('id_propietario', $user->id)->get();
        
        $edificioId = $request->input('edificio_id');
        $year = $request->input('year', now()->year);

        if ($edificioId || $edificioId === '') {
            $consumoData = $this->getConsumoData($edificioId, $year);
            $alertasData = $this->getAlertasData($edificioId, $year);
            $consumosData = $this->getConsumosData($edificioId, $year);

            return view('propietario.reportes', compact(
                'edificios', 'edificioId', 'year', 'consumoData', 'alertasData', 'consumosData'
            ));
        }

        return view('propietario.reportes', compact('edificios', 'edificioId', 'year'));
    }

    public function exportarReportePdf(Request $request)
    {
        $user = auth()->user();
        $edificioId = $request->input('edificio_id');
        $year = $request->input('year', now()->year);

        $edificio = null;
        if ($edificioId) {
            $edificio = Edificio::where('id', $edificioId)
                ->where('id_propietario', $user->id)
                ->firstOrFail();
        }

        $consumoData = $this->getConsumoData($edificioId, $year);
        $alertasData = $this->getAlertasData($edificioId, $year);
        $consumosData = $this->getConsumosData($edificioId, $year);

        $pdf = Pdf::loadView('propietario.reportes.reporte-pdf', compact(
            'user', 'edificio', 'year', 'consumoData', 'alertasData', 'consumosData'
        ));

        $filename = $edificio 
            ? "reporte-{$edificio->nombre}-{$year}.pdf" 
            : "reporte-todos-edificios-{$year}.pdf";

        return $pdf->download($filename);
    }

    public function exportarReporteExcel(Request $request)
    {
        $user = auth()->user();
        $edificioId = $request->input('edificio_id');
        $year = $request->input('year', now()->year);

        return Excel::download(
            new PropietarioReporteExport($user, $edificioId, $year), 
            "reporte-{$year}.xlsx"
        );
    }

    public function exportarReporteDetalladoExcel(Request $request)
    {
        $user = auth()->user();
        $edificioId = $request->input('edificio_id');
        $year = $request->input('year', now()->year);

        return Excel::download(
            new PropietarioReporteDetalladoExport($user, $edificioId, $year), 
            "reporte-detallado-{$year}.xlsx"
        );
    }

    // Métodos auxiliares para obtener datos
    private function getConsumoData($edificioId, $year)
    {
        $query = \App\Models\ConsumoAgua::join('medidor', 'consumo_agua.id_medidor', '=', 'medidor.id')
            ->join('departamento', 'medidor.id_departamento', '=', 'departamento.id')
            ->join('edificio', 'departamento.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', auth()->id())
            ->whereYear('consumo_agua.fecha_hora', $year);

        if ($edificioId) {
            $query->where('edificio.id', $edificioId);
        }

        // CAMBIO: usar consumo_intervalo_m3 en lugar de volumen
        return $query->selectRaw('MONTH(consumo_agua.fecha_hora) as mes, SUM(consumo_agua.consumo_intervalo_m3) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    }

    private function getAlertasData($edificioId, $year)
    {
        $query = Alerta::join('medidor', 'alerta.id_medidor', '=', 'medidor.id')
            ->join('departamento', 'medidor.id_departamento', '=', 'departamento.id')
            ->join('edificio', 'departamento.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', auth()->id())
            ->whereYear('alerta.fecha_hora', $year);

        if ($edificioId) {
            $query->where('edificio.id', $edificioId);
        }

        return $query->selectRaw('MONTH(alerta.fecha_hora) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    }
    private function getConsumosData($edificioId, $year)
    {
        $query = ConsumoEdificio::join('edificio', 'consumo_edificio.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', auth()->id())
            ->whereYear('consumo_edificio.fecha_emision', $year);

        if ($edificioId) {
            $query->where('edificio.id', $edificioId);
        }

        return $query->selectRaw('MONTH(consumo_edificio.fecha_emision) as mes, SUM(consumo_edificio.monto_total) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    }
    // REPORTES DE EDIFICIOS
    public function reporteNotasConsumo(Request $request, $edificioId)
    {
        $user = auth()->user();
        $edificio = Edificio::where('id', $edificioId)
            ->where('id_propietario', $user->id)
            ->firstOrFail();

        $mes = $request->input('mes', now()->format('Y-m'));
        
        $notasConsumo = $this->getNotasConsumoPorDepartamento($edificio, $mes);

        return view('propietario.reportes.notas-consumo', compact('edificio', 'notasConsumo', 'mes'));
    }

    /**
     * Obtener datos de notas de consumo por departamento
     */
    private function getNotasConsumoPorDepartamento($edificio, $mes)
    {
        return ConsumoDepartamento::whereHas('consumoEdificio', function($query) use ($edificio, $mes) {
                $query->where('id_edificio', $edificio->id)
                    ->where('periodo', $mes);
            })
            ->with([
                'departamento.residentes',
                'consumoEdificio'
            ])
            ->get()
            ->map(function($consumoDepto) {
                $residentes = $consumoDepto->departamento->residentes;
                $residentesNombres = $residentes->pluck('nombre')->implode(', ');
                
                return [
                    'departamento' => $consumoDepto->departamento->numero_departamento,
                    'residentes' => $residentesNombres,
                    'cantidad_residentes' => $residentes->count(),
                    'consumo_m3' => $consumoDepto->consumo_m3,
                    'porcentaje_consumo' => $consumoDepto->porcentaje_consumo,
                    'monto_asignado' => $consumoDepto->monto_asignado,
                    'estado' => $consumoDepto->estado,
                    'fecha_emision' => $consumoDepto->consumoEdificio->fecha_emision,
                    'fecha_vencimiento' => $consumoDepto->consumoEdificio->fecha_vencimiento,
                    'periodo' => $consumoDepto->consumoEdificio->periodo
                ];
            })
            ->sortBy('departamento')
            ->values()
            ->toArray();
    }
    public function exportarNotasConsumoPdf(Request $request, $edificioId)
    {
        $user = auth()->user();
        $edificio = Edificio::where('id', $edificioId)
            ->where('id_propietario', $user->id)
            ->firstOrFail();

        $mes = $request->input('mes', now()->format('Y-m'));
        
        $notasConsumo = $this->getNotasConsumoPorDepartamento($edificio, $mes);

        $pdf = Pdf::loadView('propietario.reportes.notas-consumo-pdf', compact(
            'edificio', 'notasConsumo', 'mes'
        ));

        $filename = "notas-consumo-{$edificio->nombre}-{$mes}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportarNotasConsumoExcel(Request $request, $edificioId)
    {
        $user = auth()->user();
        $edificio = Edificio::where('id', $edificioId)
            ->where('id_propietario', $user->id)
            ->firstOrFail();

        $mes = $request->input('mes', now()->format('Y-m'));
        
        $notasConsumo = $this->getNotasConsumoPorDepartamento($edificio, $mes);

        return Excel::download(
            new NotasConsumoExport($edificio, $notasConsumo, $mes),
            "notas-consumo-{$edificio->nombre}-{$mes}.xlsx"
        );
    }
    // Agrega estos métodos en tu PropietarioController

    /**
     * Reporte de todas las notas de consumo
     */
    public function reporteTodasNotasConsumo(Request $request)
    {
        $user = auth()->user();
        $edificios = Edificio::where('id_propietario', $user->id)->get();
        
        $edificioId = $request->input('edificio_id');
        $estado = $request->input('estado');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Obtener todas las notas de consumo con filtros
        $notasConsumo = $this->getTodasNotasConsumo($user->id, $edificioId, $estado, $fechaInicio, $fechaFin);

        $estadisticas = $this->calcularEstadisticasNotas($notasConsumo);

        return view('propietario.reportes.todas-notas-consumo', compact(
            'edificios',
            'notasConsumo',
            'estadisticas',
            'edificioId',
            'estado',
            'fechaInicio',
            'fechaFin'
        ));
    }

    /**
     * Exportar reporte completo a PDF
     */
    public function exportarTodasNotasConsumoPdf(Request $request)
    {
        $user = auth()->user();
        
        $edificioId = $request->input('edificio_id');
        $estado = $request->input('estado');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $edificio = null;
        if ($edificioId) {
            $edificio = Edificio::where('id', $edificioId)
                ->where('id_propietario', $user->id)
                ->first();
        }

        $notasConsumo = $this->getTodasNotasConsumo($user->id, $edificioId, $estado, $fechaInicio, $fechaFin);
        $estadisticas = $this->calcularEstadisticasNotas($notasConsumo);

        $pdf = Pdf::loadView('propietario.reportes.todas-notas-consumo-pdf', compact(
            'user', 'edificio', 'notasConsumo', 'estadisticas', 'fechaInicio', 'fechaFin', 'estado'
        ));

        $filename = $edificio 
            ? "reporte-notas-consumo-{$edificio->nombre}.pdf" 
            : "reporte-todas-notas-consumo.pdf";

        return $pdf->download($filename);
    }

    /**
     * Exportar reporte completo a Excel
     */
    public function exportarTodasNotasConsumoExcel(Request $request)
    {
        $user = auth()->user();
        
        $edificioId = $request->input('edificio_id');
        $estado = $request->input('estado');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $notasConsumo = $this->getTodasNotasConsumo($user->id, $edificioId, $estado, $fechaInicio, $fechaFin);

        return Excel::download(
            new TodasNotasConsumoExport($user, $notasConsumo, $edificioId, $estado, $fechaInicio, $fechaFin),
            "reporte-todas-notas-consumo.xlsx"
        );
    }

    /**
     * Obtener todas las notas de consumo con filtros
     */
    private function getTodasNotasConsumo($propietarioId, $edificioId = null, $estado = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = ConsumoDepartamento::whereHas('consumoEdificio.edificio', function($query) use ($propietarioId) {
                $query->where('id_propietario', $propietarioId);
            })
            ->with([
                'consumoEdificio.edificio',
                'departamento.residentes'
            ]);

        // Aplicar filtros
        if ($edificioId) {
            $query->whereHas('consumoEdificio', function($query) use ($edificioId) {
                $query->where('id_edificio', $edificioId);
            });
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($fechaInicio) {
            $query->whereHas('consumoEdificio', function($query) use ($fechaInicio) {
                $query->whereDate('fecha_emision', '>=', $fechaInicio);
            });
        }

        if ($fechaFin) {
            $query->whereHas('consumoEdificio', function($query) use ($fechaFin) {
                $query->whereDate('fecha_emision', '<=', $fechaFin);
            });
        }

        return $query->get()
            ->map(function($consumoDepto) {
                $residentes = $consumoDepto->departamento->residentes;
                $residentesNombres = $residentes->pluck('nombre')->implode(', ');

                return [
                    'id' => $consumoDepto->id,
                    'edificio' => $consumoDepto->consumoEdificio->edificio->nombre,
                    'departamento' => $consumoDepto->departamento->numero_departamento,
                    'residentes' => $residentesNombres,
                    'cantidad_residentes' => $residentes->count(),
                    'consumo_m3' => $consumoDepto->consumo_m3,
                    'porcentaje_consumo' => $consumoDepto->porcentaje_consumo,
                    'monto_asignado' => $consumoDepto->monto_asignado,
                    'estado' => $consumoDepto->estado,
                    'fecha_emision' => $consumoDepto->consumoEdificio->fecha_emision,
                    'fecha_vencimiento' => $consumoDepto->consumoEdificio->fecha_vencimiento,
                    'fecha_pago' => $consumoDepto->fecha_pago,
                    'periodo' => $consumoDepto->consumoEdificio->periodo,
                    'consumo_edificio_id' => $consumoDepto->consumoEdificio->id
                ];
            })
            ->sortBy(['edificio', 'departamento'])
            ->values()
            ->toArray();
    }

    /**
     * Calcular estadísticas de las notas de consumo
     */
    private function calcularEstadisticasNotas($notasConsumo)
    {
        $total = count($notasConsumo);
        $totalMonto = collect($notasConsumo)->sum('monto_asignado');
        $totalConsumo = collect($notasConsumo)->sum('consumo_m3');
        
        $estados = collect($notasConsumo)->groupBy('estado')->map->count();
        
        $montoPorEstado = collect($notasConsumo)->groupBy('estado')->map(function($items) {
            return collect($items)->sum('monto_asignado');
        });

        return [
            'total_notas' => $total,
            'total_monto' => $totalMonto,
            'total_consumo' => $totalConsumo,
            'estados' => $estados,
            'monto_por_estado' => $montoPorEstado,
            'promedio_monto' => $total > 0 ? $totalMonto / $total : 0
        ];
    }
}