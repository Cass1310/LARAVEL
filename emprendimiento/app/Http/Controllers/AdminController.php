<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Edificio;
use App\Models\Suscripcion;
use App\Models\Alerta;
use App\Models\Mantenimiento;
use App\Models\ConsumoAgua;
use App\Models\SuscripcionPago;
use App\Models\Gateway;
use App\Models\Medidor;
use App\Models\FacturaEdificio;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Métricas generales
        $metricas = [
            'total_propietarios' => User::where('rol', 'propietario')->count(),
            'total_residentes' => User::where('rol', 'residente')->count(),
            'total_edificios' => Edificio::count(),
            'suscripciones_activas' => Suscripcion::where('estado', 'activa')
                ->where('fecha_fin', '>=', now())
                ->count(),
            'suscripciones_vencidas' => Suscripcion::where('estado', 'activa')
                ->where('fecha_fin', '<', now())
                ->count(),
            'alertas_pendientes' => Alerta::where('estado', 'pendiente')->count(),
            'mantenimientos_pendientes' => Mantenimiento::count(),
        ];

        // Suscripciones con información de clientes
        $suscripciones = Suscripcion::with(['cliente.user', 'pagos'])
            ->orderBy('fecha_fin', 'asc')
            ->get();

        // Propietarios con sus edificios
        $propietarios = User::where('rol', 'propietario')
            ->with(['cliente.suscripciones', 'edificiosPropietario.departamentos.residentes'])
            ->get();

        // Consumo de todos los edificios
        $consumoData = $this->getConsumoGlobal();

        return view('admin.dashboard', compact(
            'metricas',
            'suscripciones',
            'propietarios',
            'consumoData'
        ));
    }

    private function getConsumoGlobal()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return [
            'consumo_mensual' => ConsumoAgua::whereYear('fecha_hora', $currentYear)
                ->whereMonth('fecha_hora', $currentMonth)
                ->sum('volumen'),
            'consumo_anual' => ConsumoAgua::whereYear('fecha_hora', $currentYear)
                ->sum('volumen'),
            'promedio_mensual' => ConsumoAgua::whereYear('fecha_hora', $currentYear)
                ->average('volumen'),
            'edificio_mayor_consumo' => Edificio::with(['departamentos.medidores.consumos' => function($query) use ($currentMonth) {
                $query->whereMonth('fecha_hora', $currentMonth);
            }])
            ->get()
            ->map(function($edificio) {
                $totalConsumo = 0;
                foreach ($edificio->departamentos as $departamento) {
                    foreach ($departamento->medidores as $medidor) {
                        $totalConsumo += $medidor->consumos->sum('volumen');
                    }
                }
                $edificio->total_consumo = $totalConsumo;
                return $edificio;
            })
            ->sortByDesc('total_consumo')
            ->first()
        ];
    }
    // En AdminController
    public function usuarios()
    {
        $usuarios = User::with(['creador', 'departamentosResidente', 'edificiosPropietario'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function crearUsuario()
    {
        return view('admin.usuarios.crear');
    }

    public function guardarUsuario(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'rol' => 'required|in:administrador,propietario,residente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
        ]);

        User::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'rol' => $validated['rol'],
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion'],
            'created_by' => auth()->id()
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente');
    }

    public function alertas()
    {
        $alertas = Alerta::with(['medidor.departamento.edificio.propietario'])
            ->orderBy('fecha_hora', 'desc')
            ->paginate(20);

        return view('admin.alertas', compact('alertas'));
    }

    // MÉTODOS DE EDIFICIOS
    public function edificios()
    {
        $edificios = Edificio::with(['propietario', 'departamentos.residentes', 'departamentos.medidores'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.edificios.index', compact('edificios'));
    }

    public function crearEdificio()
    {
        $propietarios = User::where('rol', 'propietario')->get();
        return view('admin.edificios.crear', compact('propietarios'));
    }

    public function guardarEdificio(Request $request)
    {
        $validated = $request->validate([
            'id_propietario' => 'required|exists:users,id',
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
        ]);

        Edificio::create([
            'id_propietario' => $validated['id_propietario'],
            'nombre' => $validated['nombre'],
            'direccion' => $validated['direccion'],
            'created_by' => auth()->id()
        ]);

        return redirect()->route('admin.edificios')->with('success', 'Edificio creado exitosamente');
    }

    // MÉTODOS DE SUSCRIPCIONES
    public function suscripciones()
    {
        $suscripciones = Suscripcion::with(['cliente.user', 'pagos'])
            ->orderBy('fecha_fin', 'asc')
            ->get();

        return view('admin.suscripciones.index', compact('suscripciones'));
    }

    // MÉTODOS DE ALERTAS

    public function resolverAlerta(Alerta $alerta)
    {
        $alerta->update(['estado' => 'resuelta']);
        return redirect()->back()->with('success', 'Alerta marcada como resuelta');
    }

    // MÉTODOS DE MANTENIMIENTOS
    public function mantenimientos()
    {
        $mantenimientos = Mantenimiento::with(['medidor.departamento.edificio'])
            ->orderBy('fecha', 'asc')
            ->paginate(20);

        return view('admin.mantenimientos.index', compact('mantenimientos'));
    }

    public function completarMantenimiento(Mantenimiento $mantenimiento)
    {
        $mantenimiento->update(['completado' => true, 'fecha_completado' => now()]);
        return redirect()->back()->with('success', 'Mantenimiento marcado como completado');
    }

    // MÉTODOS DE REPORTES
    public function reportes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $reporteData = $this->generarReporteCompleto($fechaInicio, $fechaFin);

        return view('admin.reportes.index', compact('reporteData', 'fechaInicio', 'fechaFin'));
    }

    private function generarReporteCompleto($fechaInicio, $fechaFin)
    {
        return [
            'consumo' => $this->getReporteConsumo($fechaInicio, $fechaFin),
            'facturacion' => $this->getReporteFacturacion($fechaInicio, $fechaFin),
            'alertas' => $this->getReporteAlertas($fechaInicio, $fechaFin),
            'mantenimientos' => $this->getReporteMantenimientos($fechaInicio, $fechaFin),
            'suscripciones' => $this->getReporteSuscripciones(),
        ];
    }

    private function getReporteConsumo($fechaInicio, $fechaFin)
    {
        return ConsumoAgua::whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->selectRaw('SUM(volumen) as total, AVG(volumen) as promedio, COUNT(*) as registros')
            ->first();
    }

    private function getReporteFacturacion($fechaInicio, $fechaFin)
    {
        return FacturaEdificio::whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->selectRaw('SUM(monto_total) as total, COUNT(*) as cantidad, AVG(monto_total) as promedio')
            ->first();
    }

    private function getReporteAlertas($fechaInicio, $fechaFin)
    {
        return Alerta::whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->selectRaw('COUNT(*) as total, tipo_alerta, estado')
            ->groupBy('tipo_alerta', 'estado')
            ->get();
    }

    private function getReporteMantenimientos($fechaInicio, $fechaFin)
    {
        return Mantenimiento::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('COUNT(*) as total, tipo, cobertura, SUM(costo) as costo_total')
            ->groupBy('tipo', 'cobertura')
            ->get();
    }

    private function getReporteSuscripciones()
    {
        return Suscripcion::selectRaw('COUNT(*) as total, tipo, estado, SUM(
            CASE WHEN tipo = "anual" THEN 99.90 * 12 ELSE 129.90 END
        ) as ingresos_totales')
            ->groupBy('tipo', 'estado')
            ->get();
    }

    // MÉTODOS ADICIONALES PARA GESTIÓN COMPLETA

    public function crearDepartamento()
    {
        $edificios = Edificio::all();
        return view('admin.departamentos.crear', compact('edificios'));
    }

    public function guardarDepartamento(Request $request)
    {
        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificio,id',
            'numero_departamento' => 'required|string|max:20',
            'piso' => 'required|string|max:10',
        ]);

        Departamento::create([
            'id_edificio' => $validated['id_edificio'],
            'numero_departamento' => $validated['numero_departamento'],
            'piso' => $validated['piso'],
            'created_by' => auth()->id()
        ]);

        return redirect()->route('admin.edificios')->with('success', 'Departamento creado exitosamente');
    }

    public function crearMedidor()
    {
        $departamentos = Departamento::with('edificio')->get();
        $gateways = Gateway::all();
        return view('admin.medidores.crear', compact('departamentos', 'gateways'));
    }

    public function guardarMedidor(Request $request)
    {
        $validated = $request->validate([
            'id_departamento' => 'required|exists:departamento,id',
            'id_gateway' => 'required|exists:gateway,id',
            'codigo_lorawan' => 'required|string|max:100|unique:medidor,codigo_lorawan',
            'fecha_instalacion' => 'required|date',
        ]);

        Medidor::create([
            'id_departamento' => $validated['id_departamento'],
            'id_gateway' => $validated['id_gateway'],
            'codigo_lorawan' => $validated['codigo_lorawan'],
            'fecha_instalacion' => $validated['fecha_instalacion'],
            'estado' => 'activo',
            'created_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Medidor creado exitosamente');
    }

    public function crearGateway()
    {
        return view('admin.gateways.crear');
    }

    public function guardarGateway(Request $request)
    {
        $validated = $request->validate([
            'codigo_gateway' => 'required|string|max:50|unique:gateway,codigo_gateway',
            'descripcion' => 'nullable|string|max:200',
            'ubicacion' => 'required|string|max:200',
        ]);

        Gateway::create($validated);

        return redirect()->back()->with('success', 'Gateway creado exitosamente');
    }

    public function gestionarPagosSuscripcion($suscripcionId)
    {
        $suscripcion = Suscripcion::with('pagos')->findOrFail($suscripcionId);
        return view('admin.suscripciones.pagos', compact('suscripcion'));
    }

    public function registrarPagoSuscripcion(Request $request, $suscripcionId)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0',
            'periodo' => 'required|string|max:20',
            'metodo_pago' => 'required|string|max:50',
        ]);

        SuscripcionPago::create([
            'id_suscripcion' => $suscripcionId,
            'monto' => $validated['monto'],
            'periodo' => $validated['periodo'],
            'estado' => 'pagado',
            'fecha_pago' => now(),
            'metodo_pago' => $validated['metodo_pago']
        ]);

        return redirect()->back()->with('success', 'Pago registrado exitosamente');
    }

    public function editarUsuario(User $user)
    {
        return view('admin.usuarios.editar', compact('user'));
    }

    public function actualizarUsuario(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'rol' => 'required|in:administrador,propietario,residente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
        ]);

        $user->update($validated);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado exitosamente');
    }

    public function eliminarUsuario(User $user)
    {
        $user->delete();
        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado exitosamente');
    }
}