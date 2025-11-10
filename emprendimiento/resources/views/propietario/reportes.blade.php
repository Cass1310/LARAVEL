<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reportes y Estadísticas') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-filter me-2"></i>Filtros de Reportes
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('propietario.reportes') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" name="edificio_id">
                                <option value="">Todos los edificios</option>
                                @foreach($edificios as $edificio)
                                    <option value="{{ $edificio->id }}" {{ $edificioId == $edificio->id ? 'selected' : '' }}>
                                        {{ $edificio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año</label>
                            <select class="form-select" name="year">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-search me-1"></i>Generar Reporte
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($consumoData) && isset($alertasData) && isset($consumosData))
            <!-- Resumen General -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-droplet fs-1"></i>
                            <h4 class="card-title mt-2">
                                {{ number_format(array_sum($consumoData), 2) }} m³
                            </h4>
                            <p class="card-text">Consumo Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-bell fs-1"></i>
                            <h4 class="card-title mt-2">
                                {{ array_sum($alertasData) }}
                            </h4>
                            <p class="card-text">Total Alertas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-currency-dollar fs-1"></i>
                            <h4 class="card-title mt-2">
                                Bs./ {{ number_format(array_sum($consumosData), 2) }}
                            </h4>
                            <p class="card-text">Facturación Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mb-4">
                <!-- Consumo Mensual -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>Consumo Mensual de Agua (m³)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumoChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Alertas Mensuales -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bell me-2"></i>Alertas por Mes
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="alertasChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Facturación Mensual -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-currency-dollar me-2"></i>Facturación Mensual (Bs./)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="consumosChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Comparativa -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>Comparativa Anual
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="comparativaChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tablas Detalladas -->
            <div class="row">
                <!-- Tabla de Consumo -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Detalle de Consumo por Mes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Consumo (m³)</th>
                                            <th>Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalConsumo = array_sum($consumoData);
                                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                        @endphp
                                        @if ($totalConsumo == 0)
                                            <div class="alert alert-warning text-center">
                                                No hay registros de consumo para este edificio en el año seleccionado.
                                            </div>
                                        @else
                                            @for($i = 1; $i <= 12; $i++)
                                                @if(isset($consumoData[$i]))
                                                    <tr>
                                                        <td>{{ $meses[$i-1] }}</td>
                                                        <td>{{ number_format($consumoData[$i], 2) }}</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-info" 
                                                                    style="width: {{ ($consumoData[$i] / $totalConsumo) * 100 }}%">
                                                                    {{ round(($consumoData[$i] / $totalConsumo) * 100, 1) }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endfor
                                            <tr class="table-primary">
                                                <td><strong>Total</strong></td>
                                                <td><strong>{{ number_format($totalConsumo, 2) }}</strong></td>
                                                <td><strong>100%</strong></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Facturación -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Detalle de Facturación por Mes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Monto (Bs./)</th>
                                            <th>Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalFacturacion = array_sum($consumosData);
                                        @endphp
                                        @if ($totalFacturacion == 0)
                                            <div class="alert alert-warning text-center">
                                                No hay registros de facturación para este edificio en el año seleccionado.
                                            </div>
                                        @else
                                            @for($i = 1; $i <= 12; $i++)
                                                @if(isset($consumosData[$i]))
                                                    <tr>
                                                        <td>{{ $meses[$i-1] }}</td>
                                                        <td>Bs./ {{ number_format($consumosData[$i], 2) }}</td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar bg-success" 
                                                                    style="width: {{ ($consumosData[$i] / $totalFacturacion) * 100 }}%">
                                                                    {{ round(($consumosData[$i] / $totalFacturacion) * 100, 1) }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endfor
                                            <tr class="table-success">
                                                <td><strong>Total</strong></td>
                                                <td><strong>Bs./ {{ number_format($totalFacturacion, 2) }}</strong></td>
                                                <td><strong>100%</strong></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Exportación -->
            <div class="card">
                <div class="card-body text-center">
                    <button class="btn btn-outline-primary me-2" onclick="exportToPDF()">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Exportar a PDF
                    </button>
                    <button class="btn btn-outline-success me-2" onclick="exportToExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar a Excel
                    </button>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Imprimir Reporte
                    </button>
                </div>
            </div>
            @else
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1"></i>
                <h4>Selecciona los filtros para generar el reporte</h4>
                <p>Utiliza los filtros superiores para visualizar las estadísticas de consumo, alertas y consumos.</p>
            </div>
            @endif
        </div>
    </div>

    @if(isset($consumoData) && isset($alertasData) && isset($consumosData))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            
            // Preparar datos para los gráficos
            const consumoMensual = Array(12).fill(0);
            const alertasMensual = Array(12).fill(0);
            const consumosMensual = Array(12).fill(0);

            // Llenar arrays con los datos
            @foreach($consumoData as $mes => $valor)
                consumoMensual[{{ $mes }} - 1] = {{ $valor }};
            @endforeach

            @foreach($alertasData as $mes => $valor)
                alertasMensual[{{ $mes }} - 1] = {{ $valor }};
            @endforeach

            @foreach($consumosData as $mes => $valor)
                consumosMensual[{{ $mes }} - 1] = {{ $valor }};
            @endforeach

            // Gráfico de Consumo
            new Chart(document.getElementById('consumoChart'), {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [{
                        label: 'Consumo (m³)',
                        data: consumoMensual,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Metros Cúbicos (m³)'
                            }
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
                        data: alertasMensual,
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

            // Gráfico de Facturación
            new Chart(document.getElementById('consumosChart'), {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [{
                        label: 'Facturación (Bs./)',
                        data: consumosMensual,
                        backgroundColor: 'rgba(40, 167, 69, 0.6)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Soles (Bs./)'
                            }
                        }
                    }
                }
            });

            // Gráfico Comparativo
            new Chart(document.getElementById('comparativaChart'), {
                type: 'line',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'Consumo (m³)',
                            data: consumoMensual,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            yAxisID: 'y',
                            fill: false
                        },
                        {
                            label: 'Facturación (Bs./)',
                            data: consumosMensual,
                            borderColor: 'rgba(40, 167, 69, 1)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            yAxisID: 'y1',
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Consumo (m³)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Facturación (Bs./)'
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
        });

        function exportToPDF() {
            // Simular exportación a PDF
            alert('Exportando a PDF...');
            // Aquí integrarías una librería como jsPDF o hacer una llamada al backend
        }

        function exportToExcel() {
            // Simular exportación a Excel
            alert('Exportando a Excel...');
            // Aquí integrarías una librería como SheetJS o hacer una llamada al backend
        }
    </script>
    @endpush
    @endif
</x-app-layout>