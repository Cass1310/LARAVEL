<?php

namespace App\Policies;

use App\Models\FacturaEdificio;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacturaEdificioPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['administrador', 'propietario']);
    }

    public function view(User $user, FacturaEdificio $factura): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'propietario') {
            return $factura->edificio->id_propietario === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->rol === 'administrador';
    }

    public function update(User $user, FacturaEdificio $factura): bool
    {
        return $user->rol === 'administrador';
    }

    public function delete(User $user, FacturaEdificio $factura): bool
    {
        return $user->rol === 'administrador';
    }

    public function pay(User $user, FacturaEdificio $factura): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $factura->edificio->id_propietario === $user->id);
    }
}