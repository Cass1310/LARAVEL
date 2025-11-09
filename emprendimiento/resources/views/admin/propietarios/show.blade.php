<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Propietario: ') . $user->nombre }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Propietario -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Información del Propietario</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $user->nombre }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Teléfono:</strong> {{ $user->telefono ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Dirección:</strong> {{ $user->direccion ?? 'N/A' }}</p>
                            <p><strong>Total Edificios:</strong> {{ $edificios->count() }}</p>
                            <p><strong>Fecha Registro:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agregar Edificio -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Agregar Nuevo Edificio</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.edificios.crear', $user) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Edificio *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dirección *</label>
                                <input type="text" class="form-control" name="direccion" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-building me-1"></i>Crear Edificio
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Edificios -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Edificios del Propietario</h5>
                </div>
                <div class="card-body">
                    @if($edificios->count() > 0)
                        <div class="accordion" id="edificiosAccordion">
                            @foreach($edificios as $edificio)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#edificio{{ $edificio->id }}">
                                            {{ $edificio->nombre }} - {{ $edificio->direccion }}
                                            <span class="badge bg-secondary ms-2">{{ $edificio->departamentos->count() }} deptos.</span>
                                            
                                            <!-- Botón eliminar edificio -->
                                            @if($edificio->departamentos->count() == 0)
                                                <form action="{{ route('admin.edificios.eliminar', $edificio) }}" method="POST" class="ms-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('¿Eliminar este edificio?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="edificio{{ $edificio->id }}" class="accordion-collapse collapse" data-bs-parent="#edificiosAccordion">
                                        <div class="accordion-body">
                                            <!-- Agregar Departamento -->
                                            <div class="mb-4">
                                                <h6>Agregar Departamento</h6>
                                                <form action="{{ route('admin.departamentos.crear', $edificio) }}" method="POST" class="row g-3">
                                                    @csrf
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="numero_departamento" placeholder="Número" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="piso" placeholder="Piso" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-plus-circle me-1"></i>Agregar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Lista de Departamentos -->
                                            <h6>Departamentos</h6>
                                            @if($edificio->departamentos->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Número</th>
                                                                <th>Piso</th>
                                                                <th>Residentes</th>
                                                                <th>Medidores</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($edificio->departamentos as $departamento)
                                                                <tr>
                                                                    <td>{{ $departamento->numero_departamento }}</td>
                                                                    <td>{{ $departamento->piso }}</td>
                                                                    <td>
                                                                        <span class="badge bg-info">{{ $departamento->residentes->count() }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-warning">{{ $departamento->medidores->count() }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <!-- Asignar Residente -->
                                                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#asignarResidenteModal{{ $departamento->id }}">
                                                                            <i class="bi bi-person-plus"></i>
                                                                        </button>
                                                                        <!-- Botón desvincular residentes -->
                                                                        @if($departamento->residentes->count() > 0)
                                                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#desvincularResidentesModal{{ $departamento->id }}">
                                                                                <i class="bi bi-person-dash"></i>
                                                                            </button>
                                                                        @endif
                                                                        <!-- Botón eliminar departamento -->
                                                                        @if($departamento->residentes->count() == 0 && $departamento->medidores->count() == 0)
                                                                            <form action="{{ route('admin.departamentos.eliminar', $departamento) }}" method="POST" class="d-inline">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                                        onclick="return confirm('¿Eliminar este departamento?')">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </td>
                                                                </tr>

                                                                <!-- Modal Asignar Residente -->
                                                                <div class="modal fade" id="asignarResidenteModal{{ $departamento->id }}" tabindex="-1">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Asignar Residente a {{ $departamento->numero_departamento }}</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <form action="{{ route('admin.residentes.asignar', $departamento) }}" method="POST">
                                                                                @csrf
                                                                                <div class="modal-body">
                                                                                    <div class="mb-3">
                                                                                        <label class="form-label">Seleccionar Residente</label>
                                                                                        <select class="form-select" name="id_residente" required>
                                                                                            <option value="">Seleccionar residente</option>
                                                                                            @foreach($residentesDisponibles as $residente)
                                                                                                <option value="{{ $residente->id }}">{{ $residente->nombre }} - {{ $residente->email }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="mb-3">
                                                                                        <label class="form-label">Fecha Inicio</label>
                                                                                        <input type="date" class="form-control" name="fecha_inicio" required>
                                                                                    </div>
                                                                                    <div class="mb-3">
                                                                                        <label class="form-label">Fecha Fin (Opcional)</label>
                                                                                        <input type="date" class="form-control" name="fecha_fin">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                                    <button type="submit" class="btn btn-primary">Asignar</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">No hay departamentos registrados.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            Este propietario no tiene edificios registrados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>