<?php

namespace App\Policies;

use App\Models\FacturaDepartamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FacturaDepartamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol, ['administrador', 'propietario', 'residente']);
    }

    public function view(User $user, FacturaDepartamento $factura): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'propietario') {
            return $factura->facturaEdificio->edificio->id_propietario === $user->id;
        }

        if ($user->rol === 'residente') {
            return $factura->departamento->residentes->contains('id', $user->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->rol === 'administrador';
    }

    public function update(User $user, FacturaDepartamento $factura): bool
    {
        return $user->rol === 'administrador';
    }

    public function delete(User $user, FacturaDepartamento $factura): bool
    {
        return $user->rol === 'administrador';
    }

    public function pay(User $user, FacturaDepartamento $factura): bool
    {
        if ($user->rol === 'administrador') {
            return true;
        }

        if ($user->rol === 'residente') {
            return $factura->departamento->residentes->contains('id', $user->id);
        }

        return false;
    }
}