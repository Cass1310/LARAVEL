<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reportes del Sistema') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <select class="form-select" name="year" onchange="this.form.submit()">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mes</label>
                            <select class="form-select" name="month" onchange="this.form.submit()">
                                <option value="">Todos los meses</option>
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" name="edificio" onchange="this.form.submit()">
                                <option value="">Todos los edificios</option>
                                @foreach($consumoPorEdificio as $consumo)
                                    <option value="{{ $consumo['edificio'] }}" {{ request('edificio') == $consumo['edificio'] ? 'selected' : '' }}>
                                        {{ $consumo['edificio'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-outline-primary" onclick="exportarReporte()">
                                    <i class="bi bi-download me-1"></i>Exportar PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <!-- Consumo por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>Consumo por Edificio (m³)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumoEdificioChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Pagos por Edificio -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-currency-dollar me-2"></i>Pagos por Edificio (S/)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="pagosEdificioChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Consumo por Departamento -->
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>Consumo por Departamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumoDepartamentoChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas de Datos -->
            <div class="row">
                <!-- Resumen de Consumo -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">Resumen de Consumo por Edificio</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Edificio</th>
                                            <th>Propietario</th>
                                            <th>Consumo (m³)</th>
                                            <th>Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalConsumo = array_sum(array_column($consumoPorEdificio, 'consumo'));
                                        @endphp
                                        @foreach($consumoPorEdificio as $consumo)
                                            @php
                                                $porcentaje = $totalConsumo > 0 ? ($consumo['consumo'] / $totalConsumo) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $consumo['edificio'] }}</td>
                                                <td>{{ $consumo['propietario'] }}</td>
                                                <td>{{ number_format($consumo['consumo'], 2) }}</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $porcentaje }}%;" 
                                                             aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                                                            {{ number_format($porcentaje, 1) }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <td colspan="2"><strong>Total</strong></td>
                                            <td><strong>{{ number_format($totalConsumo, 2) }} m³</strong></td>
                                            <td><strong>100%</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Pagos -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Resumen de Pagos por Edificio</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Edificio</th>
                                            <th>Propietario</th>
                                            <th>Total Pagado (S/)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagosPorEdificio as $pago)
                                            <tr>
                                                <td>{{ $pago['edificio'] }}</td>
                                                <td>{{ $pago['propietario'] }}</td>
                                                <td class="text-success"><strong>{{ number_format($pago['total_pagado'], 2) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <td colspan="2"><strong>Total Recaudado</strong></td>
                                            <td><strong>S/ {{ number_format($pagosPorEdificio->sum('total_pagado'), 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
                            label: 'Pagos (S/)',
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

                // Gráfico de consumo por departamento
                const deptoData = @json($consumoPorDepartamento);
                new Chart(document.getElementById('consumoDepartamentoChart'), {
                    type: 'pie',
                    data: {
                        labels: deptoData.map(item => item.departamento + ' - ' + item.edificio),
                        datasets: [{
                            data: deptoData.map(item => item.consumo),
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            });

            function exportarReporte() {
                // Simular exportación
                alert('Funcionalidad de exportación en desarrollo');
            }
        </script>
    @endpush
</x-app-layout>