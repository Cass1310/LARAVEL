<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Dashboard - Propietario') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Métricas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <i class="bi bi-building fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_edificios'] }}</h5>
                            <p class="card-text small">Edificios</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-door-closed fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_departamentos'] }}</h5>
                            <p class="card-text small">Departamentos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-people fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['total_residentes'] }}</h5>
                            <p class="card-text small">Residentes</p>
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
                            <i class="bi bi-receipt fs-1"></i>
                            <h5 class="card-title mt-2">{{ $metricas['facturas_pendientes'] }}</h5>
                            <p class="card-text small">Facturas Pendientes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <!-- Consumo por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>Consumo por Edificio (m³)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumoEdificioChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Facturas por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>Facturación por Edificio
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="facturasChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <a href="{{ route('propietario.edificios') }}" class="card text-center text-decoration-none">
                        <div class="card-body">
                            <i class="bi bi-buildings fs-1 text-primary"></i>
                            <h6 class="card-title mt-2">Mis Edificios</h6>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('propietario.facturas') }}" class="card text-center text-decoration-none">
                        <div class="card-body">
                            <i class="bi bi-receipt fs-1 text-success"></i>
                            <h6 class="card-title mt-2">Facturación</h6>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('propietario.residentes') }}" class="card text-center text-decoration-none">
                        <div class="card-body">
                            <i class="bi bi-person-plus fs-1 text-info"></i>
                            <h6 class="card-title mt-2">Gestionar Residentes</h6>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="{{ route('propietario.reportes') }}" class="card text-center text-decoration-none">
                        <div class="card-body">
                            <i class="bi bi-graph-up fs-1 text-warning"></i>
                            <h6 class="card-title mt-2">Reportes</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Gráfico de consumo por edificio
                new Chart(document.getElementById('consumoEdificioChart'), {
                    type: 'pie',
                    data: {
                        labels: @json(array_column($consumoPorEdificio, 'edificio')),
                        datasets: [{
                            data: @json(array_column($consumoPorEdificio, 'consumo')),
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Gráfico de facturas
                const facturasData = @json($facturasData);
                const datasets = [];
                const colors = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF'];

                facturasData.forEach((item, index) => {
                    datasets.push({
                        label: item.edificio,
                        data: item.facturas,
                        backgroundColor: colors[index % colors.length]
                    });
                });

                new Chart(document.getElementById('facturasChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        datasets: datasets
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