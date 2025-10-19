<?php

namespace App\Policies;

use App\Models\ConsumoDepartamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsumoDepartamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['administrador', 'propietario', 'residente']);
    }

    public function view(User $user, ConsumoDepartamento $consumo): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'propietario') {
            return $consumo->consumoEdificio->edificio->id_propietario === $user->id;
        }

        if ($user->rol === 'residente') {
            return $consumo->departamento->residentes->contains('id', $user->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->rol === 'administrador';
    }

    public function update(User $user, ConsumoDepartamento $consumo): bool
    {
        return $user->rol === 'administrador';
    }

    public function delete(User $user, ConsumoDepartamento $consumo): bool
    {
        return $user->rol === 'administrador';
    }

    public function pay(User $user, ConsumoDepartamento $consumo): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'residente') {
            return $consumo->departamento->residentes->contains('id', $user->id);
        }

        return false;
    }
}