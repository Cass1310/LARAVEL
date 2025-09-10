<?php

namespace App\Http\Controllers;

use App\Models\FacturaDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FacturaDepartamentoController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', FacturaDepartamento::class);

        $user = auth()->user();

        if ($user->rol === 'administrador') {
            $facturas = FacturaDepartamento::with(['facturaEdificio.edificio', 'departamento'])->get();
        } elseif ($user->rol === 'propietario') {
            $facturas = FacturaDepartamento::whereHas('facturaEdificio.edificio', function ($query) use ($user) {
                $query->where('id_propietario', $user->id);
            })->with(['facturaEdificio.edificio', 'departamento'])->get();
        } else {
            $facturas = FacturaDepartamento::whereHas('departamento.residentes', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->with(['facturaEdificio.edificio', 'departamento'])->get();
        }

        return view('facturas-departamento.index', compact('facturas'));
    }

    public function show(FacturaDepartamento $facturaDepartamento)
    {
        Gate::authorize('view', $facturaDepartamento);

        $facturaDepartamento->load(['facturaEdificio.edificio', 'departamento.residentes']);
        
        return view('facturas-departamento.show', compact('facturaDepartamento'));
    }

    public function pay(FacturaDepartamento $facturaDepartamento)
    {
        Gate::authorize('pay', $facturaDepartamento);

        $facturaDepartamento->markAsPaid();

        // Verificar si todas las facturas del edificio están pagadas
        $todasPagadas = !$facturaDepartamento->facturaEdificio->facturasDepartamento()
            ->where('estado', '!=', 'pagado')
            ->exists();

        if ($todasPagadas) {
            $facturaDepartamento->facturaEdificio->update(['estado' => 'pagada']);
        }

        return redirect()->back()->with('success', 'Pago registrado exitosamente');
    }
}