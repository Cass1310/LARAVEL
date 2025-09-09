<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use Illuminate\Http\Request;

class EdificioController extends Controller
{
    public function index()
    {
        $edificios = Edificio::with('departamentos')->get();
        return view('edificios.index', compact('edificios'));
    }

    public function show(Edificio $edificio)
    {
        $edificio->load('departamentos');
        return view('edificios.show', compact('edificio'));
    }

}
