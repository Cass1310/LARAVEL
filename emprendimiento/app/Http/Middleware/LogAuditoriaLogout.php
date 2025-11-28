<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditoriaService;
use Symfony\Component\HttpFoundation\Response;

class LogAuditoriaLogout
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si es una ruta de logout y el usuario está autenticado
        if ($request->routeIs('logout') && auth()->check()) {
            AuditoriaService::logout();
        }

        return $response;
    }
}