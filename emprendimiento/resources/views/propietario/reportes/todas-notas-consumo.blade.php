<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Reporte de Todas las Notas de Consumo') }}
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
                    <form action="{{ route('propietario.reportes.todas-notas-consumo') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="edificio_id" class="form-label">Edificio</label>
                            <select class="form-select" id="edificio_id" name="edificio_id">
                                <option value="">Todos los edificios</option>
                                @foreach($edificios as $edificio)
                                    <option value="{{ $edificio->id }}" {{ $edificioId == $edificio->id ? 'selected' : '' }}>
                                        {{ $edificio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ $estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="pagado" {{ $estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('propietario.reportes.todas-notas-consumo') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen Estadístico -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Notas</h6>
                            <h4>{{ $estadisticas['total_notas'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Monto</h6>
                            <h4>Bs./ {{ number_format($estadisticas['total_monto'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Consumo</h6>
                            <h4>{{ number_format($estadisticas['total_consumo'], 2) }} m³</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6 class="card-title">Promedio por Nota</h6>
                            <h4>Bs./ {{ number_format($estadisticas['promedio_monto'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución por Estado -->
            <div class="row mb-4">
                @foreach($estadisticas['estados'] as $estadoItem => $cantidad)
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title text-{{ $estadoItem == 'pagado' ? 'success' : 'warning' }}">
                                    {{ ucfirst($estadoItem) }}
                                </h6>
                                <h4>{{ $cantidad }}</h4>
                                <small class="text-muted">
                                    Bs./ {{ number_format($estadisticas['monto_por_estado'][$estadoItem] ?? 0, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botones de Exportación -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Exportar Reporte Completo</h6>
                            <p class="text-muted">Descarga el reporte con todos los filtros aplicados</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('propietario.reportes.todas-notas-consumo.exportar-pdf', request()->query()) }}" 
                               class="btn btn-danger me-2" target="_blank">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </a>
                            <a href="{{ route('propietario.reportes.todas-notas-consumo.exportar-excel', request()->query()) }}" 
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
                        Lista de Notas de Consumo 
                        @if($edificioId || $estado || $fechaInicio || $fechaFin)
                            <small class="text-warning">(Filtros aplicados)</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($notasConsumo) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Edificio</th>
                                        <th>Departamento</th>
                                        <th>Residentes</th>
                                        <th>Consumo (m³)</th>
                                        <th>Porcentaje</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Período</th>
                                        <th>Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notasConsumo as $nota)
                                        <tr>
                                            <td>
                                                <strong>{{ $nota['edificio'] }}</strong>
                                            </td>
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
                                                <small>{{ $nota['periodo'] }}</small>
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($nota['fecha_vencimiento'])->format('d/m/Y') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <td colspan="3"><strong>TOTALES</strong></td>
                                        <td class="text-end"><strong>{{ number_format($estadisticas['total_consumo'], 2) }} m³</strong></td>
                                        <td class="text-end"><strong>100%</strong></td>
                                        <td class="text-end"><strong>Bs./ {{ number_format($estadisticas['total_monto'], 2) }}</strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay notas de consumo que coincidan con los filtros seleccionados.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Botón Volver -->
            <div class="mt-4">
                <a href="{{ route('propietario.consumos') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Notas de Consumo
                </a>
            </div>
        </div>
    </div>
</x-app-layout>