<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Symfony\Component\HttpFoundation\Response;

class CheckSuscripcion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if ($user->rol === 'propietario') {
            $cliente = Cliente::where('id', $user->id)->first();
            $suscripcionActiva = $cliente?->suscripcionActiva();

            if (!$suscripcionActiva && !$request->is('suscripcion*')) {
                return redirect()->route('suscripcion.index')
                    ->with('warning', 'Necesitas una suscripción activa para acceder a esta funcionalidad');
            }
        }

        return $next($request);
    }
}
