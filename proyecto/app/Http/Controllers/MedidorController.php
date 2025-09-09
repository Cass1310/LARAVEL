<?php

namespace App\Http\Controllers;

use App\Models\Medidor;
use Illuminate\Http\Request;

class MedidorController extends Controller
{
    public function index()
    {
        $medidores = Medidor::with('departamento')->get();
        return view('medidores.index', compact('medidores'));
    }

    public function show($id)
    {
        $medidor = Medidor::with(['departamento', 'consumos', 'alertas'])->findOrFail($id);
        return view('medidores.show', compact('medidor'));
    }

}
