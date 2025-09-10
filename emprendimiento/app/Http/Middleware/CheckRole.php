<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->rol !== $role) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        return $next($request);
    }
}