<?php

namespace App\Http\Controllers;

use App\Models\User;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('departamentos')->get(); 
        return view('usuarios.index', compact('usuarios'));
    }
}
