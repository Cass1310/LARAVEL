<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Edificio: ') . $edificio->nombre }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Edificio -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $edificio->nombre }}</p>
                            <p><strong>Dirección:</strong> {{ $edificio->direccion }}</p>
                            <p><strong>Total Departamentos:</strong> {{ $edificio->departamentos->count() }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Residentes:</strong> 
                                {{ $edificio->departamentos->sum(fn($depto) => $depto->residentes->count()) }}
                            </p>
                            <p><strong>Total Medidores:</strong> 
                                {{ $edificio->departamentos->sum(fn($depto) => $depto->medidores->count()) }}
                            </p>
                            <p><strong>Propietario:</strong> {{ $edificio->propietario->nombre }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Gráfica de Consumo por Residente -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pie-chart me-2"></i>Distribución de Consumo por Residente
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(!empty($consumoPorResidente) && count($consumoPorResidente) > 0)
                                <div class="w-full h-64">
                                    <canvas id="consumoResidenteChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Consumo del mes actual ({{ now()->format('F Y') }})
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay datos de consumo para este mes.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- consumos Pendientes por Residente -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-receipt me-2"></i>Notas de consumo del Mes por Departamento
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(!empty($consumosResidentes) && count($consumosResidentes) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Departamento</th>
                                                <th>Residentes</th>
                                                <th>Consumo</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($consumosResidentes as $consumo)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $consumo['departamento'] }}</div>
                                                        @if($consumo['total_residentes'] > 2)
                                                            <small class="text-muted">
                                                                <i class="bi bi-people me-1"></i>{{ $consumo['total_residentes'] }} residentes
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $consumo['residente'] }}</small>
                                                    </td>
                                                    <td>
                                                        <small>{{ number_format($consumo['consumo_m3'], 2) }} m³</small>
                                                        <br>
                                                        <span class="badge bg-light text-dark">
                                                            {{ number_format($consumo['porcentaje_consumo'], 1) }}%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success">
                                                            Bs./ {{ number_format($consumo['monto_asignado'], 2) }}
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $consumo['estado'] == 'pagado' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($consumo['estado']) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        Período: {{ now()->format('F Y') }}
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay consumos generadas para este mes.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Resumen Financiero -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Resumen Financiero del Mes</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                @php
                                    $totalconsumodo = collect($consumosResidentes)->sum('monto_asignado');
                                    $totalPagado = collect($consumosResidentes)
                                        ->where('estado', 'pagado')
                                        ->sum('monto_asignado');
                                    $totalPendiente = $totalconsumodo - $totalPagado;
                                @endphp
                                
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Total consumodo</h6>
                                        <h4 class="text-primary">Bs./ {{ number_format($totalconsumodo, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Total Pagado</h6>
                                        <h4 class="text-success">Bs./ {{ number_format($totalPagado, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Total Pendiente</h6>
                                        <h4 class="text-warning">Bs./ {{ number_format($totalPendiente, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Volver -->
            <div class="mt-4">
                <a href="{{ route('propietario.edificios') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Edificios
                </a>
            </div>
        </div>
    </div>

    @if(count($consumoPorResidente) > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const consumoData = @json($consumoPorResidente);
                
                // Limitar la longitud de las etiquetas para mejor visualización
                const labels = consumoData.map(item => 
                    `Depto ${item.departamento}`
                );
                const data = consumoData.map(item => item.consumo);
                
                // Tooltips más informativos
                const tooltipLabels = consumoData.map(item => 
                    `Depto ${item.departamento}: ${item.residente}`
                );

                // Colores para la gráfica
                const backgroundColors = [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                    '#8AC926', '#1982C4', '#6A4C93', '#F15BB5'
                ];

                new Chart(document.getElementById('consumoResidenteChart'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors.slice(0, labels.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 10
                                    },
                                    generateLabels: function(chart) {
                                        const data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map((label, i) => {
                                                const meta = chart.getDatasetMeta(0);
                                                const style = meta.controller.getStyle(i);
                                                
                                                return {
                                                    text: label,
                                                    fillStyle: style.backgroundColor,
                                                    strokeStyle: style.borderColor,
                                                    lineWidth: style.borderWidth,
                                                    hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                    index: i
                                                };
                                            });
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = tooltipLabels[context.dataIndex] || '';
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
        </script>
        @endpush
    @endif
</x-app-layout>