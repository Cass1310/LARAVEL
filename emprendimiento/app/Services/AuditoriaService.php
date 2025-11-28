<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    public static function log($accion, $modulo = null, $descripcion = null, $datosAnteriores = null, $datosNuevos = null)
    {
        $user = Auth::user();
        $request = app('request');

        return Auditoria::create([
            'user_id' => $user ? $user->id : null,
            'user_nombre' => $user ? $user->nombre : 'Sistema',
            'user_email' => $user ? $user->email : null,
            'user_rol' => $user ? $user->rol : 'sistema',
            'accion' => $accion,
            'modulo' => $modulo,
            'descripcion' => $descripcion,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
        ]);
    }

    public static function login()
    {
        return self::log('login', 'sistema', 'Inicio de sesión exitoso');
    }

    public static function logout()
    {
        return self::log('logout', 'sistema', 'Cierre de sesión');
    }

    public static function crear($modulo, $descripcion, $datosNuevos = null)
    {
        return self::log('crear', $modulo, $descripcion, null, $datosNuevos);
    }

    public static function actualizar($modulo, $descripcion, $datosAnteriores, $datosNuevos)
    {
        return self::log('actualizar', $modulo, $descripcion, $datosAnteriores, $datosNuevos);
    }

    public static function eliminar($modulo, $descripcion, $datosAnteriores = null)
    {
        return self::log('eliminar', $modulo, $descripcion, $datosAnteriores, null);
    }

    public static function error($modulo, $descripcion)
    {
        return self::log('error', $modulo, $descripcion);
    }
}