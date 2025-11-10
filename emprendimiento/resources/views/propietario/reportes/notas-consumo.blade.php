<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reporte de Notas de Consumo - ') . $edificio->nombre }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Filtros del Reporte</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('propietario.reportes.notas-consumo', $edificio->id) }}" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="mes" class="form-label">Mes del Reporte</label>
                            <input type="month" 
                                   class="form-control" 
                                   id="mes" 
                                   name="mes" 
                                   value="{{ $mes }}"
                                   max="{{ now()->format('Y-m') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('propietario.reportes.notas-consumo.exportar-pdf', ['edificio' => $edificio->id, 'mes' => $mes]) }}" 
                                class="btn btn-danger me-2" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </a>
                            <a href="{{ route('propietario.reportes.notas-consumo.exportar-excel', ['edificio' => $edificio->id, 'mes' => $mes]) }}" 
                            class="btn btn-success">
                                <i class="bi bi-file-excel me-1"></i>Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Departamentos</h6>
                            <h4>{{ count($notasConsumo) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Monto</h6>
                            <h4>Bs./ {{ number_format(collect($notasConsumo)->sum('monto_asignado'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Consumo</h6>
                            <h4>{{ number_format(collect($notasConsumo)->sum('consumo_m3'), 2) }} m³</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6 class="card-title">Pagados</h6>
                            <h4>{{ collect($notasConsumo)->where('estado', 'pagado')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Exportación -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Exportar Reporte</h6>
                            <p class="text-muted">Descarga el reporte en diferentes formatos</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('propietario.reportes.notas-consumo.exportar-pdf', ['edificio' => $edificio->id]) }}?mes={{ $mes }}" 
                            class="btn btn-danger me-2" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </a>
                            <a href="{{ route('propietario.reportes.notas-consumo.exportar-excel', ['edificio' => $edificio->id]) }}?mes={{ $mes }}" 
                            class="btn btn-success">
                                <i class="bi bi-file-excel me-1"></i>Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Notas de Consumo -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        Notas de Consumo - {{ \Carbon\Carbon::parse($mes)->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($notasConsumo) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Departamento</th>
                                        <th>Residentes</th>
                                        <th>Consumo (m³)</th>
                                        <th>Porcentaje</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notasConsumo as $nota)
                                        <tr>
                                            <td>
                                                <strong>Depto {{ $nota['departamento'] }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $nota['residentes'] }}</small>
                                                @if($nota['cantidad_residentes'] > 1)
                                                    <span class="badge bg-info ms-1">{{ $nota['cantidad_residentes'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ number_format($nota['consumo_m3'], 2) }}</td>
                                            <td class="text-end">{{ number_format($nota['porcentaje_consumo'], 1) }}%</td>
                                            <td class="text-end">
                                                <strong class="text-success">Bs./ {{ number_format($nota['monto_asignado'], 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $nota['estado'] == 'pagado' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($nota['estado']) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($nota['fecha_vencimiento'])->format('d/m/Y') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <td colspan="2"><strong>TOTALES</strong></td>
                                        <td class="text-end"><strong>{{ number_format(collect($notasConsumo)->sum('consumo_m3'), 2) }} m³</strong></td>
                                        <td class="text-end"><strong>100%</strong></td>
                                        <td class="text-end"><strong>Bs./ {{ number_format(collect($notasConsumo)->sum('monto_asignado'), 2) }}</strong></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay notas de consumo para el mes seleccionado.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Botón Volver -->
            <div class="mt-4">
                <a href="{{ route('propietario.edificios.show', $edificio->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Edificio
                </a>
            </div>
        </div>
    </div>
</x-app-layout>