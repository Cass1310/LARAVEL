<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Mantenimientos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Historial de Mantenimientos</h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" onchange="filtrarMantenimientos()" id="filtroTipo">
                                <option value="">Todos</option>
                                <option value="preventivo">Preventivo</option>
                                <option value="correctivo">Correctivo</option>
                                <option value="instalacion">Instalación</option>
                                <option value="calibracion">Calibración</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cobertura</label>
                            <select class="form-select" onchange="filtrarMantenimientos()" id="filtroCobertura">
                                <option value="">Todas</option>
                                <option value="incluido_suscripcion">Incluido Suscripción</option>
                                <option value="cobrado">Cobrado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" onchange="filtrarMantenimientos()" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="completado">Completado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" onchange="filtrarMantenimientos()" id="filtroEdificio">
                                <option value="">Todos</option>
                                @foreach($mantenimientos->pluck('medidor.departamento.edificio.nombre')->unique() as $edificio)
                                    <option value="{{ $edificio }}">{{ $edificio }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.mantenimientos.exportar-pdf') }}" class="btn btn-outline-danger">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </a>
                        <a href="{{ route('admin.mantenimientos.exportar-excel') }}" class="btn btn-outline-success">
                            <i class="bi bi-file-excel me-1"></i>Excel
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaMantenimientos">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Medidor</th>
                                    <th>Departamento</th>
                                    <th>Edificio</th>
                                    <th>Tipo</th>
                                    <th>Cobertura</th>
                                    <th>Costo</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mantenimientos as $mantenimiento)
                                    <tr data-tipo="{{ $mantenimiento->tipo }}" data-estado="{{ $mantenimiento->estado }}" data-cobertura="{{ $mantenimiento->cobertura }}" data-edificio="{{ $mantenimiento->medidor->departamento->edificio->nombre }}">
                                        <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            <strong>{{ $mantenimiento->medidor->codigo_lorawan }}</strong>
                                        </td>
                                        <td>{{ $mantenimiento->medidor->departamento->numero_departamento }}</td>
                                        <td>{{ $mantenimiento->medidor->departamento->edificio->nombre }}</td>
                                        <td>
                                            <span class="badge bg-{{ $mantenimiento->tipo == 'preventivo' ? 'info' : ($mantenimiento->tipo == 'correctivo' ? 'warning' : 'secondary') }} text-capitalize">
                                                {{ $mantenimiento->tipo }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $mantenimiento->cobertura == 'incluido_suscripcion' ? 'success' : 'primary' }}">
                                                {{ str_replace('_', ' ', ucfirst($mantenimiento->cobertura)) }}
                                            </span>
                                        </td>
                                        <td>Bs.  {{ number_format($mantenimiento->costo, 2) }}</td>
                                        <td>{{ Str::limit($mantenimiento->descripcion, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $mantenimiento->estado == 'pendiente' ? 'warning' : 
                                                ($mantenimiento->estado == 'en_proceso' ? 'info' : 
                                                ($mantenimiento->estado == 'completado' ? 'success' : 'danger')) 
                                            }}">
                                                {{ str_replace('_', ' ', ucfirst($mantenimiento->estado)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal{{ $mantenimiento->id }}">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                            </div>
                                            <div class="modal fade" id="cambiarEstadoModal{{ $mantenimiento->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Cambiar Estado - Mantenimiento</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.mantenimientos.atender', $mantenimiento) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nuevo Estado</label>
                                                                    <select class="form-select" name="estado" required>
                                                                        <option value="en_proceso" {{ $mantenimiento->estado == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                                                        <option value="completado" {{ $mantenimiento->estado == 'completado' ? 'selected' : '' }}>Completado</option>
                                                                        <option value="cancelado" {{ $mantenimiento->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function filtrarMantenimientos() {
                const tipo = document.getElementById('filtroTipo').value;
                const cobertura = document.getElementById('filtroCobertura').value;
                const estado = document.getElementById('filtroEstado').value;
                const edificio = document.getElementById('filtroEdificio').value;
                
                const filas = document.querySelectorAll('#tablaMantenimientos tbody tr');
                
                filas.forEach(fila => {
                    let mostrar = true;
                    
                    if (tipo && fila.dataset.tipo !== tipo) {
                        mostrar = false;
                    }
                    
                    if (cobertura && fila.dataset.cobertura !== cobertura) {
                        mostrar = false;
                    }
                    
                    if (estado && fila.dataset.estado !== estado) {
                        mostrar = false;
                    }
                    
                    if (edificio && fila.dataset.edificio !== edificio) {
                        mostrar = false;
                    }
                    
                    fila.style.display = mostrar ? '' : 'none';
                });
            }
        </script>
    @endpush
</x-app-layout>