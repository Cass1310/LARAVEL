<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::query();

        // Filtros
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $estadisticas = [
            'total' => Auditoria::count(),
            'logins' => Auditoria::where('accion', 'login')->count(),
            'logouts' => Auditoria::where('accion', 'logout')->count(),
            'creaciones' => Auditoria::where('accion', 'crear')->count(),
            'actualizaciones' => Auditoria::where('accion', 'actualizar')->count(),
            'eliminaciones' => Auditoria::where('accion', 'eliminar')->count(),
        ];

        return view('admin.auditoria.index', compact('logs', 'estadisticas'));
    }

    public function show($id)
    {
        $log = Auditoria::findOrFail($id);
        return view('admin.auditoria.show', compact('log'));
    }

    public function usuario($userId)
    {
        $logs = Auditoria::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $usuarioNombre = $logs->first()->user_nombre ?? 'Usuario Eliminado';

        return view('admin.auditoria.usuario', compact('logs', 'usuarioNombre', 'userId'));
    }
}