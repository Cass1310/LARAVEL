<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\Medidor;
use App\Models\User;
use App\Models\ConsumoDepartamento;
use App\Models\ConsumoEdificio;
use App\Policies\EdificioPolicy;
use App\Policies\DepartamentoPolicy;
use App\Policies\ConsumoEdificioPolicy;
use App\Policies\ConsumoDepartamentoPolicy;
use App\Policies\MedidorPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Edificio::class => EdificioPolicy::class,
        Departamento::class => DepartamentoPolicy::class,
        Medidor::class => MedidorPolicy::class,
        ConsumoEdificio::class => ConsumoEdificioPolicy::class,
        ConsumoDepartamento::class => ConsumoDepartamentoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Definir gates globales
        Gate::define('is-admin', function (User $user) {
            return $user->rol === 'administrador';
        });

        Gate::define('is-propietario', function (User $user) {
            return $user->rol === 'propietario';
        });

        Gate::define('is-residente', function (User $user) {
            return $user->rol === 'residente';
        });
        Gate::define('pay-consumo', function (User $user, ConsumoDepartamento $consumo) {
            return $user->rol === 'administrador' || 
                ($user->rol === 'residente' && $consumo->departamento->residentes->contains('id', $user->id));
        });
        // NUEVO GATE PARA PAGAR CONSUMOS
        Gate::define('pay-consumo', function (User $user, ConsumoEdificio $consumo) {
            return $user->rol === 'propietario' && 
                   $consumo->edificio->id_propietario === $user->id &&
                   $consumo->estado === 'pendiente';
        });

        // GATE PARA CREAR CONSUMOS
        Gate::define('create-consumo', function (User $user, Edificio $edificio) {
            return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
        });

        // GATE PARA GESTIONAR RESIDENTES
        Gate::define('manage-residentes', function (User $user, Edificio $edificio) {
            return $user->rol === 'propietario' && $edificio->id_propietario === $user->id;
        });
    }
}