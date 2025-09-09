<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index()
    {
        $alertas = Alerta::with('medidor')->get();
        return view('alertas.index', compact('alertas'));
    }

    public function show(Alerta $alerta)
    {
        $alerta->load('medidor');
        return view('alertas.show', compact('alerta'));
    }

    public function resolver(Alerta $alerta)
    {
        $alerta->estado = 'resuelta';
        $alerta->save();

        return redirect()->back()->with('success', 'Alerta marcada como resuelta.');
    }

}
