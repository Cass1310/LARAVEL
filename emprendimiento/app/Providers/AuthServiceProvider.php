<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Edificio;
use App\Models\Departamento;
use App\Models\Medidor;
use App\Models\User;
use App\Models\FacturaDepartamento;
use App\Policies\EdificioPolicy;
use App\Policies\DepartamentoPolicy;
use App\Policies\MedidorPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Edificio::class => EdificioPolicy::class,
        Departamento::class => DepartamentoPolicy::class,
        Medidor::class => MedidorPolicy::class,
        FacturaEdificio::class => FacturaEdificioPolicy::class,
        FacturaDepartamento::class => FacturaDepartamentoPolicy::class,
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
        Gate::define('pay-factura', function (User $user, FacturaDepartamento $factura) {
        return $user->rol === 'administrador' || 
               ($user->rol === 'residente' && $factura->departamento->residentes->contains('id', $user->id));
    });
    }
}