<?php

namespace App\Policies;

use App\Models\ConsumoEdificio;
use App\Models\User;
use App\Models\Edificio;
use Illuminate\Auth\Access\Response;

class ConsumoEdificioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->rol === 'propietario' || $user->rol === 'administrador';
    }

    public function view(User $user, ConsumoEdificio $consumoEdificio): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $consumoEdificio->edificio->id_propietario === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->rol === 'propietario' || $user->rol === 'administrador';
    }

    public function update(User $user, ConsumoEdificio $consumoEdificio): bool
    {
        return $user->rol === 'administrador' || 
               ($user->rol === 'propietario' && $consumoEdificio->edificio->id_propietario === $user->id);
    }

    public function delete(User $user, ConsumoEdificio $consumoEdificio): bool
    {
        return $user->rol === 'administrador';
    }

    // NUEVO MÉTODO PARA PAGAR CONSUMOS
    public function pay(User $user, ConsumoEdificio $consumoEdificio): bool
    {
        // Solo propietarios pueden pagar sus propios consumos y solo si están pendientes
        return $user->rol === 'propietario' && 
               $consumoEdificio->edificio->id_propietario === $user->id &&
               $consumoEdificio->estado === 'pendiente';
    }

    // MÉTODO PARA CREAR CONSUMOS (si no existe)
    public function createConsumo(User $user, Edificio $edificio): bool
    {
        return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
    }
}