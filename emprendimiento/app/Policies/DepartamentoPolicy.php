<?php

namespace App\Policies;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['administrador', 'propietario', 'residente']);
    }

    public function view(User $user, Departamento $departamento): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'propietario') {
            return $departamento->edificio->id_propietario === $user->id;
        }

        if ($user->rol === 'residente') {
            return $departamento->residentes->contains('id', $user->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->rol === 'administrador' || $user->rol === 'propietario';
    }

    public function update(User $user, Departamento $departamento): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $departamento->edificio->id_propietario === $user->id);
    }

    public function delete(User $user, Departamento $departamento): bool
    {
        return $user->rol === 'administrador';
    }
}