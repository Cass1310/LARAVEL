<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4 text-dark">
            {{ __('Dashboard - Residentes') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            @if($sin_departamento)
                <div class="alert alert-warning mb-4">
                    <strong>Información importante</strong>
                    <p class="mb-0">No tienes un departamento asignado actualmente. Contacta con el administrador.</p>
                </div>
            @else
                <!-- Métricas rápidas -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-primary bg-opacity-25">
                                    <i class="bi bi-bar-chart-fill text-primary fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Consumo del Mes</p>
                                    <h5 class="mb-0 fw-bold">{{ $metricas['consumo_mes_actual'] }} m³</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-danger bg-opacity-25">
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Alertas Pendientes</p>
                                    <h5 class="mb-0 fw-bold text-danger">{{ $metricas['alertas_pendientes'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-warning bg-opacity-25">
                                    <i class="bi bi-tools text-warning fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Mantenimientos</p>
                                    <h5 class="mb-0 fw-bold">{{ $metricas['mantenimientos_pendientes'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica de consumo y consumo -->
                <div class="row g-4 mb-4">
                    <!-- Gráfica de consumo -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-sm p-3 h-100">
                            <h5 class="fw-semibold mb-3">Distribución de Consumo</h5>
                            <div style="height: 250px;">
                                <canvas id="consumoChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Información de consumo -->
                    <div class="col-12 col-lg-6">
                        <div class="card shadow-sm p-3 h-100">
                            <h5 class="fw-semibold mb-3">Estado de consumo</h5>
                            @if($consumo)
                                <div class="d-flex flex-column gap-2">
                                    <div>
                                        <small class="text-muted">Período</small>
                                        <p class="fw-semibold mb-0">{{ $consumo->consumoEdificio->periodo }}</p>
                                    </div>
                                    <div>
                                        <small class="text-muted">Monto a Pagar</small>
                                        <p class="fs-4 fw-bold text-primary mb-0">Bs./ {{ number_format($consumo->monto_asignado, 2) }}</p>
                                    </div>
                                    <div>
                                        <small class="text-muted">Estado</small>
                                        <span class="badge 
                                            {{ $consumo->estado == 'pagado' ? 'bg-success' : 
                                               ($consumo->estado == 'pendiente' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ ucfirst($consumo->estado) }}
                                        </span>
                                    </div>
                                    <div>
                                        <small class="text-muted">Consumo del mes</small>
                                        <p class="fw-semibold mb-0">{{ $consumo->consumo_m3 }} m³ ({{ $consumo->porcentaje_consumo }}%)</p>
                                    </div>
                                    @if($consumo->estado == 'pendiente')
                                        <button class="btn btn-primary mt-2">
                                            Pagar consumo
                                        </button>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted mb-0">No hay consumo para el mes actual.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('residente.departamento') }}" class="card shadow-sm p-3 text-decoration-none text-dark h-100">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-success bg-opacity-25">
                                    <i class="bi bi-house-door-fill text-success fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Mi Departamento</p>
                                    <h6 class="fw-semibold mb-0">Ver detalles</h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('residente.alertas') }}" class="card shadow-sm p-3 text-decoration-none text-dark h-100">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-danger bg-opacity-25">
                                    <i class="bi bi-exclamation-circle-fill text-danger fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Alertas</p>
                                    <h6 class="fw-semibold mb-0">Ver alertas</h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('residente.mantenimientos') }}" class="card shadow-sm p-3 text-decoration-none text-dark h-100">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle bg-warning bg-opacity-25">
                                    <i class="bi bi-wrench-adjustable-circle-fill text-warning fs-3"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-1 text-muted small">Mantenimiento</p>
                                    <h6 class="fw-semibold mb-0">Solicitar</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$sin_departamento)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('consumoChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Mi Consumo', 'Resto del Edificio'],
                            datasets: [{
                                data: [{{ $consumoData['porcentaje'] }}, {{ 100 - $consumoData['porcentaje'] }}],
                                backgroundColor: ['#0d6efd', '#dee2e6'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.raw + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
