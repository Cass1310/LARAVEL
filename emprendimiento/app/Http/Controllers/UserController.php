<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('is-admin');

        $users = User::with('creador')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        Gate::authorize('is-admin');
        return view('users.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('is-admin');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'rol' => 'required|in:administrador,propietario,residente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['created_by'] = auth()->id();

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }
}