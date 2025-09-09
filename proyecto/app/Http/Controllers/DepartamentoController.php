<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::with('edificio', 'usuario')->get();
        return view('departamentos.index', compact('departamentos'));
    }

    public function show(Departamento $departamento)
    {
        $departamento->load('edificio', 'usuario', 'medidores');
        return view('departamentos.show', compact('departamento'));
    }
}
