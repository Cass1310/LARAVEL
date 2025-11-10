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
                            <h5 class="card-title mt-2">{{ $metricas['total_usuarios'] }}</h5>
                            <p class="card-text small">Total Usuarios</p>
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
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_medidores'] }}</h5>
                            <p class="card-text small">Medidores</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-bell fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['alertas_pendientes'] }}</h5>
                            <p class="card-text small">Alertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body">
                            <i class="bi bi-tools fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['mantenimientos_pendientes'] }}</h5>
                            <p class="card-text small">Mantenimientos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-secondary text-white">
                        <div class="card-body">
                            <i class="bi bi-credit-card fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['suscripciones_activas'] }}</h5>
                            <p class="card-text small">Suscripciones</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Principales -->
            <div class="row mb-4">
                <!-- Consumo por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-water me-2"></i>Consumo por Edificio (m³)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumoEdificioChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pagos por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-currency-dollar me-2"></i>Pagos por Edificio
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="pagosEdificioChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas y Mantenimientos Recientes -->
            <div class="row">
                <!-- Alertas Recientes -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bell me-2"></i>Alertas Recientes
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($alertasRecientes->count() > 0)
                                <div class="list-group">
                                    @foreach($alertasRecientes as $alerta)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 text-capitalize">{{ $alerta->tipo_alerta }}</h6>
                                                <small>{{ $alerta->fecha_hora->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-1">
                                                <strong>Edificio:</strong> {{ $alerta->medidor->departamento->edificio->nombre }}<br>
                                                <strong>Departamento:</strong> {{ $alerta->medidor->departamento->numero_departamento }}<br>
                                                <strong>Valor:</strong> {{ $alerta->valor_detectado }} m³
                                            </p>
                                            <form action="{{ route('admin.alertas.atender', $alerta) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check-circle me-1"></i>Marcar como Resuelta
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No hay alertas pendientes.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Mantenimientos Recientes -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-tools me-2"></i>Mantenimientos Recientes
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($mantenimientosRecientes->count() > 0)
                                <div class="list-group">
                                    @foreach($mantenimientosRecientes as $mantenimiento)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 text-capitalize">{{ $mantenimiento->tipo }}</h6>
                                                <small>{{ $mantenimiento->fecha->format('d/m/Y') }}</small>
                                            </div>
                                            <p class="mb-1">
                                                <strong>Edificio:</strong> {{ $mantenimiento->medidor->departamento->edificio->nombre }}<br>
                                                <strong>Departamento:</strong> {{ $mantenimiento->medidor->departamento->numero_departamento }}<br>
                                                <strong>Costo:</strong> S/ {{ number_format($mantenimiento->costo, 2) }}
                                            </p>
                                            <form action="{{ route('admin.mantenimientos.atender', $mantenimiento) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check-circle me-1"></i>Marcar como Atendido
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No hay mantenimientos recientes.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gráfico de consumo por edificio
                const consumoData = @json($consumoPorEdificio);
                new Chart(document.getElementById('consumoEdificioChart'), {
                    type: 'bar',
                    data: {
                        labels: consumoData.map(item => item.edificio),
                        datasets: [{
                            label: 'Consumo (m³)',
                            data: consumoData.map(item => item.consumo),
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Gráfico de pagos por edificio
                const pagosData = @json($pagosPorEdificio);
                new Chart(document.getElementById('pagosEdificioChart'), {
                    type: 'bar',
                    data: {
                        labels: pagosData.map(item => item.edificio),
                        datasets: [{
                            label: 'Pagos (Bs. )',
                            data: pagosData.map(item => item.total_pagado),
                            backgroundColor: 'rgba(40, 167, 69, 0.6)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>