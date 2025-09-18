<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Dashboard - Administrador') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Métricas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <i class="bi bi-people fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_propietarios'] }}</h5>
                            <p class="card-text small">Propietarios</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-person fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_residentes'] }}</h5>
                            <p class="card-text small">Residentes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-building fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_edificios'] }}</h5>
                            <p class="card-text small">Edificios</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-shield-check fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['suscripciones_activas'] }}</h5>
                            <p class="card-text small">Suscripciones Activas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body">
                            <i class="bi bi-bell fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['alertas_pendientes'] }}</h5>
                            <p class="card-text small">Alertas Pendientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-secondary text-white">
                        <div class="card-body">
                            <i class="bi bi-tools fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['mantenimientos_pendientes'] }}</h5>
                            <p class="card-text small">Mantenimientos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Consumo -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-droplet fs-1"></i>
                            <h5 class="card-title mt-2">{{ number_format($consumoData['consumo_mensual'], 2) }} m³</h5>
                            <p class="card-text">Consumo Mensual</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <i class="bi bi-water fs-1"></i>
                            <h5 class="card-title mt-2">{{ number_format($consumoData['consumo_anual'], 2) }} m³</h5>
                            <p class="card-text">Consumo Anual</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-graph-up fs-1"></i>
                            <h5 class="card-title mt-2">{{ number_format($consumoData['promedio_mensual'], 2) }} m³</h5>
                            <p class="card-text">Promedio Mensual</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-trophy fs-1"></i>
                            <h5 class="card-title mt-2">
                                {{ $consumoData['edificio_mayor_consumo']->nombre ?? 'N/A' }}
                            </h5>
                            <p class="card-text">Mayor Consumo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suscripciones -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-credit-card me-2"></i>Estado de Suscripciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                    <th>Días Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suscripciones as $suscripcion)
                                    @php
                                        $dias = (int) now()->diffInDays($suscripcion->fecha_fin, false);
                                    @endphp
                                    <tr>
                                        <td>{{ $suscripcion->cliente->user->nombre }}</td>
                                        <td>
                                            <span class="badge bg-primary text-capitalize">
                                                {{ $suscripcion->tipo }}
                                            </span>
                                        </td>
                                        <td>{{ $suscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $suscripcion->fecha_fin->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $suscripcion->estado == 'activa' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($suscripcion->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $dias < 15 ? 'warning' : 'success' }}">
                                                @if($dias > 0) {{ $dias }} días
                                                @elseif($dias == 0) Hoy vence
                                                @else Vencida @endif
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Propietarios y Edificios -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-buildings me-2"></i>Propietarios y sus Edificios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Propietario</th>
                                    <th>Email</th>
                                    <th>Edificios</th>
                                    <th>Departamentos</th>
                                    <th>Residentes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propietarios as $propietario)
                                    <tr>
                                        <td>{{ $propietario->nombre }}</td>
                                        <td>{{ $propietario->email }}</td>
                                        <td>{{ $propietario->edificiosPropietario->count() }}</td>
                                        <td>{{ $propietario->edificiosPropietario->sum('departamentos.count') }}</td>
                                        <td>{{ $propietario->edificiosPropietario->sum(function($edificio) {
                                            return $edificio->departamentos->sum('residentes.count');
                                        }) }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>