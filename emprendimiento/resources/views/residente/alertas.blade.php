<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Alertas de Mi Departamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($departamento)
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET" action="{{ route('residente.alertas') }}">
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                    <option value="atendida" {{ request('estado') == 'atendida' ? 'selected' : '' }}>Atendidas</option>
                                    <option value="resuelta" {{ request('estado') == 'resuelta' ? 'selected' : '' }}>Resueltas</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="fuga" {{ request('tipo') == 'fuga' ? 'selected' : '' }}>Fuga</option>
                                    <option value="consumo_brusco" {{ request('tipo') == 'consumo_brusco' ? 'selected' : '' }}>Consumo Brusco</option>
                                    <option value="consumo_excesivo" {{ request('tipo') == 'consumo_excesivo' ? 'selected' : '' }}>Consumo Excesivo</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" name="fecha_desde" value="{{ request('fecha_desde') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter me-1"></i>Filtrar
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Lista de Alertas -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell me-2"></i>Alertas Registradas
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($alertas->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha/Hora</th>
                                            <th>Medidor</th>
                                            <th>Tipo</th>
                                            <th>Valor Detectado</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alertas as $alerta)
                                            <tr>
                                                <td>{{ $alerta->fecha_hora->format('d/m/Y H:i') }}</td>
                                                <td>{{ $alerta->medidor->codigo_lorawan }}</td>
                                                <td>
                                                    @switch($alerta->tipo_alerta)
                                                        @case('fuga')
                                                            <span class="badge bg-danger">Fuga</span>
                                                            @break
                                                        @case('consumo_brusco')
                                                            <span class="badge bg-warning text-dark">Consumo Brusco</span>
                                                            @break
                                                        @case('consumo_excesivo')
                                                            <span class="badge bg-info">Consumo Excesivo</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>{{ number_format($alerta->valor_detectado, 2) }} m³</td>
                                                <td>
                                                    <span class="badge bg-{{ $alerta->estado == 'pendiente' ? 'warning' : ($alerta->estado == 'atendida' ? 'info' : 'success') }}">
                                                        {{ ucfirst($alerta->estado) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#alertaModal{{ $alerta->id }}">
                                                        <i class="bi bi-eye me-2"> Ver</i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal -->
                                            <div class="modal fade" id="alertaModal{{ $alerta->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detalles de Alerta</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Medidor:</strong> {{ $alerta->medidor->codigo_lorawan }}</p>
                                                            <p><strong>Tipo:</strong> {{ ucfirst($alerta->tipo_alerta) }}</p>
                                                            <p><strong>Valor:</strong> {{ number_format($alerta->valor_detectado, 2) }} m³</p>
                                                            <p><strong>Fecha:</strong> {{ $alerta->fecha_hora->format('d/m/Y H:i') }}</p>
                                                            <p><strong>Estado:</strong> {{ ucfirst($alerta->estado) }}</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $alertas->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No se encontraron alertas para tu departamento.
                            </div>
                        @endif
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
</x-app-layout>