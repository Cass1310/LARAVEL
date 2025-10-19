<?php

namespace App\Http\Controllers;

use App\Models\ConsumoDepartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ConsumoDepartamentoController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', ConsumoDepartamento::class);

        $user = auth()->user();

        if ($user->rol === 'administrador') {
            $consumos = ConsumoDepartamento::with(['consumoEdificio.edificio', 'departamento'])->get();
        } elseif ($user->rol === 'propietario') {
            $consumos = ConsumoDepartamento::whereHas('consumoEdificio.edificio', function ($query) use ($user) {
                $query->where('id_propietario', $user->id);
            })->with(['consumoEdificio.edificio', 'departamento'])->get();
        } else {
            $consumos = ConsumoDepartamento::whereHas('departamento.residentes', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->with(['consumoEdificio.edificio', 'departamento'])->get();
        }

        return view('consumos-departamento.index', compact('consumos'));
    }

    public function show(ConsumoDepartamento $consumoDepartamento)
    {
        Gate::authorize('view', $consumoDepartamento);

        $consumoDepartamento->load(['consumoEdificio.edificio', 'departamento.residentes']);
        
        return view('consumos-departamento.show', compact('consumoDepartamento'));
    }

    public function pay(ConsumoDepartamento $consumoDepartamento)
    {
        Gate::authorize('pay', $consumoDepartamento);

        $consumoDepartamento->markAsPaid();

        // Verificar si todas las consumos del edificio están pagadas
        $todasPagadas = !$consumoDepartamento->consumoEdificio->consumosDepartamento()
            ->where('estado', '!=', 'pagado')
            ->exists();

        if ($todasPagadas) {
            $consumoDepartamento->consumoEdificio->update(['estado' => 'pagada']);
        }

        return redirect()->back()->with('success', 'Pago registrado exitosamente');
    }
}