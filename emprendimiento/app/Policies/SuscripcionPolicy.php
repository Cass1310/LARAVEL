<?php

namespace App\Policies;

use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SuscripcionPolicy
{
    public function view(User $user, Suscripcion $suscripcion)
    {
        return $user->id === $suscripcion->cliente->id;
    }

    public function renovar(User $user, Suscripcion $suscripcion)
    {
        return $user->id === $suscripcion->cliente->id &&
               $suscripcion->estado === 'activa' &&
               $suscripcion->fecha_fin->diffInDays(now()) <= 30;
    }

    public function cancelar(User $user, Suscripcion $suscripcion)
    {
        return $user->id === $suscripcion->cliente->id &&
               $suscripcion->estado === 'activa';
    }
    public function pagar(User $user, Suscripcion $suscripcion)
    {
        return $user->id === $suscripcion->cliente->id;
    }

    public function verPagos(User $user, Suscripcion $suscripcion)
    {
        return $user->id === $suscripcion->cliente->id;
    }
}