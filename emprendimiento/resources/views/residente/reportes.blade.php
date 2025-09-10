<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reportes y Estadísticas') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($departamento)
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Año</label>
                                <select class="form-select" name="year" onchange="this.form.submit()">
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-8">
                                <button type="button" class="btn btn-outline-primary" onclick="exportCharts()">
                                    <i class="bi bi-download me-1"></i>Exportar Reporte
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row mb-4">
                    <!-- Consumo Mensual -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bar-chart me-2"></i>Consumo Mensual (m³)
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="consumoChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas Mensuales -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bell me-2"></i>Alertas Mensuales
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="alertasChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Pagos Mensuales -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-currency-dollar me-2"></i>Pagos Mensuales (Bs./)
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="pagosChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Anual -->
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up me-2"></i>Resumen Anual {{ $year }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted">Consumo Total</h6>
                                            <h4 class="text-info">{{ array_sum($consumoMensual) }} m³</h4>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted">Total Alertas</h6>
                                            <h4 class="text-warning">{{ array_sum($alertasMensual) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted">Total Pagado</h6>
                                            <h4 class="text-success">Bs./ {{ number_format(array_sum($pagosMensual), 2) }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h6 class="text-muted">Promedio Mensual</h6>
                                            <h4 class="text-primary">Bs./ {{ number_format(array_sum($pagosMensual) / 12, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No tienes un departamento asignado actualmente.
                </div>
            @endif
        </div>
    </div>

    @if($departamento)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    
                    // Gráfico de Consumo
                    new Chart(document.getElementById('consumoChart'), {
                        type: 'bar',
                        data: {
                            labels: meses,
                            datasets: [{
                                label: 'Consumo (m³)',
                                data: @json($consumoMensual),
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

                    // Gráfico de Alertas
                    new Chart(document.getElementById('alertasChart'), {
                        type: 'line',
                        data: {
                            labels: meses,
                            datasets: [{
                                label: 'Alertas',
                                data: @json($alertasMensual),
                                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });

                    // Gráfico de Pagos
                    new Chart(document.getElementById('pagosChart'), {
                        type: 'bar',
                        data: {
                            labels: meses,
                            datasets: [{
                                label: 'Pagos (Bs./)',
                                data: @json($pagosMensual),
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

                function exportCharts() {
                    // Implementar lógica de exportación
                    alert('Funcionalidad de exportación en desarrollo');
                }
            </script>
        @endpush
    @endif
</x-app-layout>