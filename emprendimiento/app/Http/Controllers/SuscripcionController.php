<?php

namespace App\Http\Controllers;

use App\Models\Suscripcion;
use App\Models\SuscripcionPago;
use App\Models\Cliente;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;
use Carbon\Carbon;

class SuscripcionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cliente = Cliente::where('id', $user->id)->first();
        
        if (!$cliente) {
            // Crear cliente automáticamente si no existe
            $cliente = Cliente::create([
                'id' => $user->id,
                'razon_social' => $user->nombre
            ]);
        }

        $suscripciones = $cliente->suscripciones()->with('pagos')->orderBy('created_at', 'desc')->get();
        $suscripcionActiva = $cliente->suscripcionActiva();

        return view('suscripcion.index', compact('suscripciones', 'suscripcionActiva', 'cliente'));
    }

    public function crear()
    {
        $precios = [
            'mensual' => 129.90,
            'anual' => 99.90 * 12 // Precio mensual con descuento anual
        ];

        return view('suscripcion.crear', compact('precios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:mensual,anual',
            'metodo_pago' => 'required|in:visa,mastercard,paypal',
            'numero_tarjeta' => 'required_if:metodo_pago,visa,mastercard',
            'fecha_vencimiento' => 'required_if:metodo_pago,visa,mastercard',
            'cvv' => 'required_if:metodo_pago,visa,mastercard'
        ]);

        $user = auth()->user();
        $cliente = Cliente::where('id', $user->id)->firstOrFail();

        // Calcular fechas
        $fechaInicio = now();
        $fechaFin = $validated['tipo'] === 'anual' 
            ? $fechaInicio->copy()->addYear() 
            : $fechaInicio->copy()->addMonth();

        // Crear suscripción
        $suscripcion = Suscripcion::create([
            'tipo' => $validated['tipo'],
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado' => 'activa',
            'id_cliente' => $cliente->id
        ]);

        // Procesar pago
        $pagoExitoso = $this->procesarPago($validated);

        if ($pagoExitoso) {
            // Crear registro de pago
            $this->crearPagoSuscripcion($suscripcion, $validated);

            return redirect()->route('suscripcion.index')
                ->with('success', 'Suscripción activada exitosamente');
        }

        $suscripcion->update(['estado' => 'vencida']);
        return back()->with('error', 'Error en el procesamiento del pago');
    }

    private function procesarPago($datosPago)
    {
        // Simular procesamiento de pago
        // En producción, integrar con gateway de pago
        return true;
    }

    private function crearPagoSuscripcion($suscripcion, $datosPago)
    {
        $monto = $suscripcion->tipo === 'anual' ? 99.90 * 12 : 129.90;
        $periodo = $suscripcion->tipo === 'anual' ? 'Anual' : 'Mensual';

        SuscripcionPago::create([
            'id_suscripcion' => $suscripcion->id,
            'periodo' => $periodo,
            'monto' => $monto,
            'estado' => 'pagado',
            'fecha_pago' => now(),
            'metodo_pago' => $datosPago['metodo_pago']
        ]);

        // Si es mensual, crear próximos pagos pendientes
        if ($suscripcion->tipo === 'mensual') {
            for ($i = 1; $i <= 11; $i++) {
                SuscripcionPago::create([
                    'id_suscripcion' => $suscripcion->id,
                    'periodo' => 'Mes ' . ($i + 1),
                    'monto' => 129.90,
                    'estado' => 'pendiente',
                    'fecha_pago' => null,
                ]);
            }
        }
    }

    public function renovar($id)
    {
        $suscripcion = Suscripcion::findOrFail($id);
        Gate::authorize('renovar', $suscripcion);

        $nuevaFechaFin = $suscripcion->fecha_fin->copy();
        if ($suscripcion->tipo === 'anual') {
            $nuevaFechaFin->addYear();
        } else {
            $nuevaFechaFin->addMonth();
        }

        $suscripcion->update([
            'fecha_fin' => $nuevaFechaFin,
            'estado' => 'activa'
        ]);

        // Crear nuevo pago
        $this->crearPagoRenovacion($suscripcion);

        return redirect()->back()->with('success', 'Suscripción renovada exitosamente');
    }

    private function crearPagoRenovacion($suscripcion)
    {
        $monto = $suscripcion->tipo === 'anual' ? 99.90 * 12 : 129.90;
        $periodo = $suscripcion->tipo === 'anual' ? 'Renovación Anual' : 'Renovación Mensual';

        SuscripcionPago::create([
            'id_suscripcion' => $suscripcion->id,
            'periodo' => $periodo,
            'monto' => $monto,
            'estado' => 'pagado',
            'fecha_pago' => now(),
            'metodo_pago' => 'renovacion_automatica'
        ]);
    }

    public function cancelar($id)
    {
        $suscripcion = Suscripcion::findOrFail($id);
        Gate::authorize('cancelar', $suscripcion);

        $suscripcion->update(['estado' => 'vencida']);

        // Marcar pagos pendientes como vencidos
        $suscripcion->pagos()
            ->where('estado', 'pendiente')
            ->update(['estado' => 'vencido']);

        return redirect()->back()->with('success', 'Suscripción cancelada');
    }

    public function pagos($id)
    {
        $suscripcion = Suscripcion::with('pagos')->findOrFail($id);
        Gate::authorize('view', $suscripcion);

        return view('suscripcion.pagos', compact('suscripcion'));
    }

    public function pagarPago($suscripcionId, $pagoId)
    {
        $pago = SuscripcionPago::where('id_suscripcion', $suscripcionId)
            ->where('id', $pagoId)
            ->firstOrFail();

        Gate::authorize('pagar', $pago->suscripcion);

        $pago->marcarComoPagado();

        return redirect()->back()->with('success', 'Pago realizado exitosamente');
    }
}