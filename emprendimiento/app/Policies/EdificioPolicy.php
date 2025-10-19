<?php

namespace App\Policies;

use App\Models\Edificio;
use App\Models\User;
use App\Models\ConsumoEdificio;
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
    public function createConsumo(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
    }

    public function payConsumo(User $user, ConsumoEdificio $consumo): bool
    {
        return $user->rol === 'propietario' && $consumo->edificio->id_propietario === $user->id;
    }

    public function manageResidentes(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
    }
}