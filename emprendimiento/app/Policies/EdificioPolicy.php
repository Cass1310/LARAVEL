<?php

namespace App\Policies;

use App\Models\Edificio;
use App\Models\User;
use App\Models\FacturaEdificio;
use Illuminate\Auth\Access\Response;

class EdificioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->rol === 'administrador' || $user->rol === 'propietario';
    }

    public function view(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $edificio->id_propietario === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->rol === 'administrador' || $user->rol === 'propietario';
    }

    public function update(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $edificio->id_propietario === $user->id);
    }

    public function delete(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'administrador';
    }
    public function createFactura(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
    }

    public function payFactura(User $user, FacturaEdificio $factura): bool
    {
        return $user->rol === 'propietario' && $factura->edificio->id_propietario === $user->id;
    }

    public function manageResidentes(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
    }
}