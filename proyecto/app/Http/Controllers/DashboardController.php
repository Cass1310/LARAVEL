<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use App\Models\Medidor;
use App\Models\ConsumoAgua;
use App\Models\Alerta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        // Recuperar edificios, medidores, consumos y alertas
        $edificios = Edificio::with('departamentos')->get();
        $medidores = Medidor::all();
        $consumos = ConsumoAgua::latest()->take(10)->get();  // Los últimos 10 consumos
        $alertas = Alerta::where('estado', 'pendiente')->latest()->take(5)->get();  // Las alertas pendientes
        $alertasActivas = Alerta::with('medidor')->where('estado', 'pendiente')->latest()->take(10)->get();
        return view('dashboard.index', compact('edificios', 'medidores', 'consumos', 'alertas', 'alertasActivas'));
    }
}
