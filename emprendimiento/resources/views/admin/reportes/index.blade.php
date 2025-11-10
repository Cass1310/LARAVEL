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
                <!-- Consumo por Edificio (Barras) -->
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

                <!-- Distribución por Edificio (Torta) -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>Distribución de Consumo por Edificio
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="distribucionEdificioChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle por Departamento -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-building me-2"></i>Detalle de Consumo por Departamento
                            </h5>
                            <button class="btn btn-sm btn-outline-dark" onclick="toggleAllDepartamentos()">
                                <i class="bi bi-arrows-expand me-1"></i>Expandir/Contraer Todos
                            </button>
                        </div>
                        <div class="card-body">
                            @foreach($consumoPorEdificio as $edificio)
                                <div class="edificio-card mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light cursor-pointer" 
                                             onclick="toggleDepartamentos('edificio-{{ $edificio['edificio_id'] ?? $loop->index }}')">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-building me-2"></i>
                                                    <strong>{{ $edificio['edificio'] }}</strong>
                                                    <span class="badge bg-primary ms-2">
                                                        Consumo Total: {{ number_format($edificio['consumo'], 2) }} m³
                                                    </span>
                                                </h6>
                                                <span class="toggle-icon">
                                                    <i class="bi bi-chevron-down"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body departamentos-container" 
                                             id="edificio-{{ $edificio['edificio_id'] ?? $loop->index }}" 
                                             style="display: none;">
                                            @php
                                                // CORRECCIÓN: Convertir el array a colección y filtrar
                                                $departamentosEdificio = collect($consumoPorDepartamento)
                                                    ->where('edificio', $edificio['edificio'])
                                                    ->values();
                                                $totalEdificio = $departamentosEdificio->sum('consumo');
                                            @endphp
                                            
                                            @if($departamentosEdificio->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Departamento</th>
                                                                <th>Residentes</th>
                                                                <th>Consumo (m³)</th>
                                                                <th>Porcentaje</th>
                                                                <th>Monto Asignado</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($departamentosEdificio as $depto)
                                                                @php
                                                                    $porcentajeDepto = $totalEdificio > 0 ? ($depto['consumo'] / $totalEdificio) * 100 : 0;
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <strong>Depto {{ $depto['departamento'] }}</strong>
                                                                    </td>
                                                                    <td>
                                                                        <small class="text-muted">{{ $depto['residentes'] ?? 'Sin residentes' }}</small>
                                                                        @if(($depto['cantidad_residentes'] ?? 0) > 1)
                                                                            <span class="badge bg-info ms-1">{{ $depto['cantidad_residentes'] }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($depto['consumo'], 2) }}</td>
                                                                    <td>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="progress flex-grow-1 me-2" style="height: 15px;">
                                                                                <div class="progress-bar bg-info" role="progressbar" 
                                                                                     style="width: {{ $porcentajeDepto }}%;" 
                                                                                     aria-valuenow="{{ $porcentajeDepto }}" 
                                                                                     aria-valuemin="0" aria-valuemax="100">
                                                                                </div>
                                                                            </div>
                                                                            <span class="text-muted small">{{ number_format($porcentajeDepto, 1) }}%</span>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-end text-success">
                                                                        <strong>Bs./ {{ number_format($depto['monto_asignado'] ?? 0, 2) }}</strong>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-{{ ($depto['estado'] ?? 'pendiente') == 'pagado' ? 'success' : 'warning' }}">
                                                                            {{ ucfirst($depto['estado'] ?? 'pendiente') }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-warning">
                                                                <td colspan="2"><strong>Total {{ $edificio['edificio'] }}</strong></td>
                                                                <td class="text-end"><strong>{{ number_format($totalEdificio, 2) }} m³</strong></td>
                                                                <td><strong>100%</strong></td>
                                                                <td class="text-end">
                                                                    <strong>Bs./ {{ number_format($departamentosEdificio->sum('monto_asignado'), 2) }}</strong>
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $pagados = $departamentosEdificio->where('estado', 'pagado')->count();
                                                                        $totalDeptos = $departamentosEdificio->count();
                                                                    @endphp
                                                                    <span class="badge bg-{{ $pagados == $totalDeptos ? 'success' : ($pagados > 0 ? 'warning' : 'danger') }}">
                                                                        {{ $pagados }}/{{ $totalDeptos }} pagados
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info text-center mb-0">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    No hay departamentos con consumo registrado en este edificio.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Pagos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Resumen Financiero por Edificio</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Edificio</th>
                                            <th>Propietario</th>
                                            <th>Total Generado (Bs.)</th>
                                            <th>Total Pagado (Bs.)</th>
                                            <th>Total Pendiente (Bs.)</th>
                                            <th>Porcentaje Pagado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagosPorEdificio as $pago)
                                            @php
                                                $porcentajePagado = $pago['total_generado'] > 0 ? ($pago['total_pagado'] / $pago['total_generado']) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $pago['edificio'] }}</td>
                                                <td>{{ $pago['propietario'] }}</td>
                                                <td class="text-end">Bs./ {{ number_format($pago['total_generado'], 2) }}</td>
                                                <td class="text-end text-success"><strong>Bs./ {{ number_format($pago['total_pagado'], 2) }}</strong></td>
                                                <td class="text-end text-warning"><strong>Bs./ {{ number_format($pago['total_pendiente'], 2) }}</strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 15px;">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                 style="width: {{ $porcentajePagado }}%;" 
                                                                 aria-valuenow="{{ $porcentajePagado }}" 
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span class="text-muted small">{{ number_format($porcentajePagado, 1) }}%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <td colspan="2"><strong>TOTAL GENERAL</strong></td>
                                            <td class="text-end"><strong>Bs./ {{ number_format($pagosPorEdificio->sum('total_generado'), 2) }}</strong></td>
                                            <td class="text-end"><strong>Bs./ {{ number_format($pagosPorEdificio->sum('total_pagado'), 2) }}</strong></td>
                                            <td class="text-end"><strong>Bs./ {{ number_format($pagosPorEdificio->sum('total_pendiente'), 2) }}</strong></td>
                                            <td>
                                                @php
                                                    $totalGenerado = $pagosPorEdificio->sum('total_generado');
                                                    $totalPagado = $pagosPorEdificio->sum('total_pagado');
                                                    $porcentajeTotal = $totalGenerado > 0 ? ($totalPagado / $totalGenerado) * 100 : 0;
                                                @endphp
                                                <strong>{{ number_format($porcentajeTotal, 1) }}%</strong>
                                            </td>
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
                // Gráfico de consumo por edificio (Barras)
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
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Consumo (m³)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Edificios'
                                }
                            }
                        }
                    }
                });

                // Gráfico de distribución por edificio (Torta)
                new Chart(document.getElementById('distribucionEdificioChart'), {
                    type: 'doughnut',
                    data: {
                        labels: consumoData.map(item => item.edificio),
                        datasets: [{
                            data: consumoData.map(item => item.consumo),
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                '#9966FF', '#FF9F40', '#8AC926', '#1982C4',
                                '#6A4C93', '#F15BB5', '#00BBF9', '#00F5D4'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} m³ (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            });

            function toggleDepartamentos(edificioId) {
                const container = document.getElementById(edificioId);
                const toggleIcon = container.previousElementSibling.querySelector('.toggle-icon i');
                
                if (container.style.display === 'none') {
                    container.style.display = 'block';
                    toggleIcon.className = 'bi bi-chevron-up';
                } else {
                    container.style.display = 'none';
                    toggleIcon.className = 'bi bi-chevron-down';
                }
            }

            function toggleAllDepartamentos() {
                const containers = document.querySelectorAll('.departamentos-container');
                const allHidden = Array.from(containers).every(container => container.style.display === 'none');
                
                containers.forEach(container => {
                    const toggleIcon = container.previousElementSibling.querySelector('.toggle-icon i');
                    if (allHidden) {
                        container.style.display = 'block';
                        toggleIcon.className = 'bi bi-chevron-up';
                    } else {
                        container.style.display = 'none';
                        toggleIcon.className = 'bi bi-chevron-down';
                    }
                });
            }

            function exportarReporte() {
                // Simular exportación
                alert('Funcionalidad de exportación en desarrollo');
            }
        </script>

        <style>
            .cursor-pointer {
                cursor: pointer;
            }
            .edificio-card .card-header {
                transition: background-color 0.3s ease;
            }
            .edificio-card .card-header:hover {
                background-color: #e9ecef !important;
            }
            .departamentos-container {
                transition: all 0.3s ease;
            }
            .toggle-icon {
                transition: transform 0.3s ease;
            }
        </style>
    @endpush
</x-app-layout>