<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\Medidor;
use App\Models\Gateway;
use App\Models\Alerta;
use App\Models\Mantenimiento;
use App\Models\Suscripcion;
use App\Models\SuscripcionPago;
use App\Models\Cliente;
use App\Models\ConsumoEdificio;
use App\Models\ConsumoDepartamento;
use App\Models\ConsumoAgua;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Métricas generales
        $metricas = [
            'total_usuarios' => User::count(),
            'total_propietarios' => User::where('rol', 'propietario')->count(),
            'total_residentes' => User::where('rol', 'residente')->count(),
            'total_edificios' => Edificio::count(),
            'total_departamentos' => Departamento::count(),
            'total_medidores' => Medidor::count(),
            'alertas_pendientes' => Alerta::where('estado', 'pendiente')->count(),
            'mantenimientos_pendientes' => Mantenimiento::count(),
            'suscripciones_activas' => Suscripcion::where('estado', 'activa')->count(),
        ];

        // Datos para gráficos
        $consumoPorEdificio = $this->getConsumoPorEdificio();
        $pagosPorEdificio = $this->getPagosPorEdificio();
        $suscripcionesData = $this->getSuscripcionesData();

        // Alertas y mantenimientos recientes
        $alertasRecientes = Alerta::with('medidor.departamento.edificio')
            ->where('estado', 'pendiente')
            ->orderBy('fecha_hora', 'desc')
            ->take(5)
            ->get();

        $mantenimientosRecientes = Mantenimiento::with('medidor.departamento.edificio')
            ->orderBy('fecha', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'metricas',
            'consumoPorEdificio',
            'pagosPorEdificio',
            'suscripcionesData',
            'alertasRecientes',
            'mantenimientosRecientes'
        ));
    }

    private function getConsumoPorEdificio()
    {
        $edificios = Edificio::with(['departamentos.medidores.consumos'])->get();
        $data = [];

        foreach ($edificios as $edificio) {
            $consumoTotal = $edificio->departamentos->sum(function($departamento) {
                return $departamento->medidores->sum(function($medidor) {
                    return $medidor->consumos()->whereMonth('fecha_hora', now()->month)->sum('volumen');
                });
            });

            $data[] = [
                'edificio' => $edificio->nombre,
                'propietario' => $edificio->propietario->nombre,
                'consumo' => $consumoTotal
            ];
        }

        return $data;
    }

    private function getPagosPorEdificio()
    {
        return ConsumoEdificio::with('edificio.propietario')
            ->where('estado', 'pagada')
            ->whereYear('fecha_emision', now()->year)
            ->get()
            ->groupBy('edificio.nombre')
            ->map(function($facturas, $edificio) {
                return [
                    'edificio' => $edificio,
                    'total_pagado' => $facturas->sum('monto_total'),
                    'propietario' => $facturas->first()->edificio->propietario->nombre
                ];
            })->values();
    }

    private function getSuscripcionesData()
    {
        return [
            'activas' => Suscripcion::where('estado', 'activa')->count(),
            'vencidas' => Suscripcion::where('estado', 'vencida')->count(),
            'mensuales' => Suscripcion::where('tipo', 'mensual')->count(),
            'anuales' => Suscripcion::where('tipo', 'anual')->count(),
        ];
    }

    public function usuarios()
    {
        $usuarios = User::with('creador')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function crearUsuario()
    {
        $edificios = Edificio::with('departamentos')->get();
        return view('admin.usuarios.crear', compact('edificios'));
    }

    public function guardarUsuario(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'rol' => 'required|in:propietario,residente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'id_departamento' => 'required_if:rol,residente|exists:departamento,id',
            'fecha_inicio' => 'required_if:rol,residente|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);

        // Crear usuario
        $usuario = User::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion'],
            'created_by' => auth()->id(),
        ]);

        // Si es residente, asignar al departamento
        if ($validated['rol'] === 'residente' && isset($validated['id_departamento'])) {
            $usuario->departamentosResidente()->attach($validated['id_departamento'], [
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin']
            ]);
        }

        // Si es propietario, crear cliente automáticamente
        if ($validated['rol'] === 'propietario') {
            Cliente::create([
                'id' => $usuario->id,
                'razon_social' => $validated['nombre']
            ]);
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente');
    }
    public function editarUsuario(User $user)
    {
        // Cargamos todos los edificios con sus departamentos
        $edificios = Edificio::with('departamentos')->get();

        // Obtenemos el departamento actual (si el residente tiene uno asignado)
        $departamentoActual = $user->departamentosResidente()->first();

        return view('admin.usuarios.editar', compact('user', 'edificios', 'departamentoActual'));
    }


    public function actualizarUsuario(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'rol' => 'required|in:administrador,propietario,residente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'id_departamento' => 'required_if:rol,residente|exists:departamento,id',
            'fecha_inicio' => 'required_if:rol,residente|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);

        $updateData = [
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'telefono' => $validated['telefono'],
            'direccion' => $validated['direccion'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Si es residente, actualizar departamento
        if ($validated['rol'] === 'residente' && isset($validated['id_departamento'])) {
            // Eliminar asignaciones anteriores
            $user->departamentosResidente()->detach();
            
            // Asignar nuevo departamento
            $user->departamentosResidente()->attach($validated['id_departamento'], [
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin']
            ]);
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado exitosamente');
    }

    public function eliminarUsuario(User $user)
    {
        // Verificar que no sea el último administrador
        if ($user->rol === 'administrador' && User::where('rol', 'administrador')->count() <= 1) {
            return redirect()->route('admin.usuarios')->with('error', 'No se puede eliminar el único administrador del sistema');
        }

        // Verificar relaciones antes de eliminar
        if ($user->rol === 'propietario' && $user->edificiosPropietario()->count() > 0) {
            return redirect()->route('admin.usuarios')->with('error', 'No se puede eliminar un propietario que tiene edificios asignados');
        }

        $user->delete();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado exitosamente');
    }

    public function equipos()
    {
        $medidores = Medidor::with(['departamento.edificio', 'gateway'])->get();
        $gateways = Gateway::all();
        
        return view('admin.equipos.index', compact('medidores', 'gateways'));
    }

    public function medidores()
    {
        $medidores = Medidor::with(['departamento.edificio', 'gateway'])->get();
        $departamentos = Departamento::with('edificio')->get();
        $gateways = Gateway::all();
        
        return view('admin.equipos.medidores', compact('medidores', 'departamentos', 'gateways'));
    }
    public function editarGateway(Gateway $gateway)
    {
        return view('admin.equipos.gateways-editar', compact('gateway'));
    }

    public function actualizarGateway(Request $request, Gateway $gateway)
    {
        $validated = $request->validate([
            'codigo_gateway' => 'required|unique:gateway,codigo_gateway,' . $gateway->id . '|max:50',
            'descripcion' => 'nullable|string|max:200',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        $gateway->update($validated);

        return redirect()->route('admin.gateways')->with('success', 'Gateway actualizado exitosamente');
    }

    public function eliminarGateway(Gateway $gateway)
    {
        // Verificar que no tenga medidores asociados
        if ($gateway->medidores()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un gateway que tiene medidores asociados');
        }

        $gateway->delete();

        return redirect()->back()->with('success', 'Gateway eliminado exitosamente');
    }
    public function atenderMantenimiento(Request $request, Mantenimiento $mantenimiento)
    {
        $validated = $request->validate([
            'estado' => 'required|in:en_proceso,completado,cancelado'
        ]);

        $mantenimiento->update(['estado' => $validated['estado']]);

        return redirect()->back()->with('success', 'Estado del mantenimiento actualizado');
    }
    public function crearMedidor()
    {
        $departamentos = Departamento::with('edificio')->get();
        $gateways = Gateway::all();
        
        return view('admin.equipos.medidores-crear', compact('departamentos', 'gateways'));
    }

    public function guardarMedidor(Request $request)
    {
        $validated = $request->validate([
            'codigo_lorawan' => 'required|unique:medidor,codigo_lorawan|max:100',
            'id_departamento' => 'required|exists:departamento,id',
            'id_gateway' => 'nullable|exists:gateway,id',
            'estado' => 'required|in:activo,inactivo',
            'fecha_instalacion' => 'required|date',
        ]);

        Medidor::create([
            'codigo_lorawan' => $validated['codigo_lorawan'],
            'id_departamento' => $validated['id_departamento'],
            'id_gateway' => $validated['id_gateway'],
            'estado' => $validated['estado'],
            'fecha_instalacion' => $validated['fecha_instalacion'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.medidores')->with('success', 'Medidor creado exitosamente');
    }

    public function asignarGateway(Request $request, Medidor $medidor)
    {
        $validated = $request->validate([
            'id_gateway' => 'required|exists:gateway,id'
        ]);

        $medidor->update(['id_gateway' => $validated['id_gateway']]);

        return redirect()->back()->with('success', 'Gateway asignado exitosamente');
    }

    public function gateways()
    {
        $gateways = Gateway::with('medidores')->get();
        return view('admin.equipos.gateways', compact('gateways'));
    }

    public function crearGateway()
    {
        return view('admin.equipos.gateways-crear');
    }

    public function guardarGateway(Request $request)
    {
        $validated = $request->validate([
            'codigo_gateway' => 'required|unique:gateway,codigo_gateway|max:50',
            'descripcion' => 'nullable|string|max:200',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        Gateway::create($validated);

        return redirect()->route('admin.gateways')->with('success', 'Gateway creado exitosamente');
    }

    public function mantenimientos()
    {
        $mantenimientos = Mantenimiento::with(['medidor.departamento.edificio'])
            ->orderBy('fecha', 'desc')
            ->get();

        return view('admin.mantenimientos.index', compact('mantenimientos'));
    }

    public function alertas()
    {
        $alertas = Alerta::with(['medidor.departamento.edificio'])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return view('admin.alertas.index', compact('alertas'));
    }

    public function atenderAlerta(Alerta $alerta)
    {
        $alerta->update(['estado' => 'resuelta']);

        return redirect()->back()->with('success', 'Alerta marcada como resuelta');
    }

    public function suscripciones()
    {
        $suscripciones = Suscripcion::with(['cliente.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.suscripciones.index', compact('suscripciones'));
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

    public function reportes()
    {
        $consumoPorEdificio = $this->getConsumoPorEdificio();
        $pagosPorEdificio = $this->getPagosPorEdificio();
        $consumoPorDepartamento = $this->getConsumoPorDepartamento();

        return view('admin.reportes.index', compact(
            'consumoPorEdificio',
            'pagosPorEdificio',
            'consumoPorDepartamento'
        ));
    }

    private function getConsumoPorDepartamento()
    {
        $departamentos = Departamento::with(['edificio', 'medidores.consumos'])->get();
        $data = [];

        foreach ($departamentos as $departamento) {
            $consumoTotal = $departamento->medidores->sum(function($medidor) {
                return $medidor->consumos()->whereMonth('fecha_hora', now()->month)->sum('volumen');
            });

            $data[] = [
                'departamento' => $departamento->numero_departamento,
                'edificio' => $departamento->edificio->nombre,
                'consumo' => $consumoTotal
            ];
        }

        return $data;
    }

    public function propietarios()
    {
        $propietarios = User::where('rol', 'propietario')
            ->withCount(['edificiosPropietario', 'elementosCreados'])
            ->get();

        return view('admin.propietarios.index', compact('propietarios'));
    }

    public function crearEdificio(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
        ]);

        Edificio::create([
            'id_propietario' => $user->id,
            'nombre' => $validated['nombre'],
            'direccion' => $validated['direccion'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Edificio creado exitosamente');
    }
    public function eliminarEdificio(Edificio $edificio)
    {
        // Verificar que no tenga departamentos
        if ($edificio->departamentos()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un edificio que tiene departamentos');
        }

        $edificio->delete();

        return redirect()->back()->with('success', 'Edificio eliminado exitosamente');
    }

    public function eliminarDepartamento(Departamento $departamento)
    {
        // Verificar que no tenga residentes
        if ($departamento->residentes()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un departamento que tiene residentes asignados');
        }

        // Verificar que no tenga medidores
        if ($departamento->medidores()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un departamento que tiene medidores asignados');
        }

        $departamento->delete();

        return redirect()->back()->with('success', 'Departamento eliminado exitosamente');
    }
    public function editarMedidor(Medidor $medidor)
    {
        $departamentos = Departamento::with('edificio')->get();
        $gateways = Gateway::all();
        
        return view('admin.equipos.medidores-editar', compact('medidor', 'departamentos', 'gateways'));
    }

    public function actualizarMedidor(Request $request, Medidor $medidor)
    {
        $validated = $request->validate([
            'codigo_lorawan' => 'required|unique:medidor,codigo_lorawan,' . $medidor->id . '|max:100',
            'id_departamento' => 'required|exists:departamento,id',
            'id_gateway' => 'nullable|exists:gateway,id',
            'estado' => 'required|in:activo,inactivo',
            'fecha_instalacion' => 'required|date',
        ]);

        $medidor->update($validated);

        return redirect()->route('admin.medidores')->with('success', 'Medidor actualizado exitosamente');
    }

    public function eliminarMedidor(Medidor $medidor)
    {
        // Verificar que no tenga consumos registrados
        if ($medidor->consumos()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un medidor que tiene registros de consumo');
        }

        // Verificar que no tenga alertas
        if ($medidor->alertas()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un medidor que tiene alertas registradas');
        }

        // Verificar que no tenga mantenimientos
        if ($medidor->mantenimientos()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un medidor que tiene mantenimientos registrados');
        }

        $medidor->delete();

        return redirect()->back()->with('success', 'Medidor eliminado exitosamente');
    }

    public function desvincularResidente(Departamento $departamento, User $residente)
    {
        $departamento->residentes()->detach($residente->id);

        return redirect()->back()->with('success', 'Residente desvinculado del departamento');
    }
    public function crearDepartamento(Request $request, Edificio $edificio)
    {
        $validated = $request->validate([
            'numero_departamento' => 'required|string|max:20',
            'piso' => 'required|string|max:10',
        ]);

        Departamento::create([
            'id_edificio' => $edificio->id,
            'numero_departamento' => $validated['numero_departamento'],
            'piso' => $validated['piso'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Departamento creado exitosamente');
    }
    public function desvincularDepartamentos(User $user)
    {
        if ($user->rol !== 'residente') {
            return redirect()->back()->with('error', 'Solo se pueden desvincular residentes');
        }

        $user->departamentosResidente()->detach();

        return redirect()->back()->with('success', 'Usuario desvinculado de todos los departamentos');
    }

    public function asignarResidente(Request $request, Departamento $departamento)
    {
        $validated = $request->validate([
            'id_residente' => 'required|exists:users,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
        ]);

        $departamento->residentes()->attach($validated['id_residente'], [
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin']
        ]);

        return redirect()->back()->with('success', 'Residente asignado exitosamente');
    }
    // Para obtener residentes disponibles en propietario show
    private function getResidentesDisponibles()
    {
        return User::where('rol', 'residente')
            ->whereDoesntHave('departamentosResidente', function($query) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            })
            ->get();
    }

    public function propietarioShow(User $user)
    {
        $edificios = $user->edificiosPropietario()->with(['departamentos.residentes', 'departamentos.medidores'])->get();
        $residentesDisponibles = $this->getResidentesDisponibles();
        
        return view('admin.propietarios.show', compact('user', 'edificios', 'residentesDisponibles'));
    }
}