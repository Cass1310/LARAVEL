<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EdificioController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Edificio::class);

        if (auth()->user()->rol === 'administrador') {
            $edificios = Edificio::with('propietario')->get();
        } else {
            $edificios = Edificio::where('id_propietario', auth()->id())
                ->with('propietario')
                ->get();
        }

        return view('edificios.index', compact('edificios'));
    }

    public function create()
    {
        Gate::authorize('create', Edificio::class);

        // Si es propietario, solo puede asignarse a sí mismo
        $propietarios = auth()->user()->rol === 'administrador' 
            ? User::where('rol', 'propietario')->get()
            : collect([auth()->user()]);

        return view('edificios.create', compact('propietarios'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Edificio::class);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
            'id_propietario' => 'required|exists:users,id',
        ]);

        // Si es propietario, forzar que sea el propio usuario
        if (auth()->user()->rol === 'propietario') {
            $validated['id_propietario'] = auth()->id();
        }

        $validated['created_by'] = auth()->id();

        Edificio::create($validated);

        return redirect()->route('edificios.index')->with('success', 'Edificio creado exitosamente');
    }

    public function show(Edificio $edificio)
    {
        Gate::authorize('view', $edificio);

        return view('edificios.show', compact('edificio'));
    }

    public function edit(Edificio $edificio)
    {
        Gate::authorize('update', $edificio);

        $propietarios = auth()->user()->rol === 'administrador' 
            ? User::where('rol', 'propietario')->get()
            : collect([auth()->user()]);

        return view('edificios.edit', compact('edificio', 'propietarios'));
    }

    public function update(Request $request, Edificio $edificio)
    {
        Gate::authorize('update', $edificio);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:200',
            'id_propietario' => 'required|exists:users,id',
        ]);

        // Si es propietario, forzar que sea el propio usuario
        if (auth()->user()->rol === 'propietario') {
            $validated['id_propietario'] = auth()->id();
        }

        $edificio->update($validated);

        return redirect()->route('edificios.index')->with('success', 'Edificio actualizado exitosamente');
    }

    public function destroy(Edificio $edificio)
    {
        Gate::authorize('delete', $edificio);

        $edificio->delete();

        return redirect()->route('edificios.index')->with('success', 'Edificio eliminado exitosamente');
    }
}