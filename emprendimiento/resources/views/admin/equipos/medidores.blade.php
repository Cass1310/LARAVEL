<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Medidores') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Medidores</h5>
                    <a href="{{ route('admin.medidores.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Medidor
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" onchange="filtrarMedidores()" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" onchange="filtrarMedidores()" id="filtroEdificio">
                                <option value="">Todos</option>
                                @foreach($medidores->pluck('departamento.edificio.nombre')->unique() as $edificio)
                                    <option value="{{ $edificio }}">{{ $edificio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gateway</label>
                            <select class="form-select" onchange="filtrarMedidores()" id="filtroGateway">
                                <option value="">Todos</option>
                                <option value="sin_gateway">Sin Gateway</option>
                                @foreach($gateways as $gateway)
                                    <option value="{{ $gateway->id }}">{{ $gateway->codigo_gateway }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaMedidores">
                            <thead>
                                <tr>
                                    <th>Código LoRaWAN</th>
                                    <th>Departamento</th>
                                    <th>Edificio</th>
                                    <th>Gateway</th>
                                    <th>Estado</th>
                                    <th>Instalación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medidores as $medidor)
                                    <tr data-estado="{{ $medidor->estado }}" data-edificio="{{ $medidor->departamento->edificio->nombre }}" data-gateway="{{ $medidor->id_gateway ?? 'sin_gateway' }}">
                                        <td>
                                            <strong>{{ $medidor->codigo_lorawan }}</strong>
                                        </td>
                                        <td>
                                            {{ $medidor->departamento->numero_departamento }} - Piso {{ $medidor->departamento->piso }}
                                        </td>
                                        <td>{{ $medidor->departamento->edificio->nombre }}</td>
                                        <td>
                                            @if($medidor->gateway)
                                                <span class="badge bg-success">{{ $medidor->gateway->codigo_gateway }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Sin Gateway</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                                {{ ucfirst($medidor->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $medidor->fecha_instalacion?->format('d/m/Y') ?? 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group">                                                
                                                <a href="{{ route('admin.medidores.editar', $medidor) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                @if($medidor->consumos()->count() == 0 && $medidor->alertas()->count() == 0 && $medidor->mantenimientos()->count() == 0)
                                                    <form action="{{ route('admin.medidores.eliminar', $medidor) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('¿Eliminar este medidor?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" title="No se puede eliminar - Tiene registros asociados">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
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
            function filtrarMedidores() {
                const estado = document.getElementById('filtroEstado').value;
                const edificio = document.getElementById('filtroEdificio').value;
                const gateway = document.getElementById('filtroGateway').value;
                
                const filas = document.querySelectorAll('#tablaMedidores tbody tr');
                
                filas.forEach(fila => {
                    let mostrar = true;
                    
                    if (estado && fila.dataset.estado !== estado) {
                        mostrar = false;
                    }
                    
                    if (edificio && fila.dataset.edificio !== edificio) {
                        mostrar = false;
                    }
                    
                    if (gateway) {
                        if (gateway === 'sin_gateway' && fila.dataset.gateway !== 'sin_gateway') {
                            mostrar = false;
                        } else if (gateway !== 'sin_gateway' && fila.dataset.gateway !== gateway) {
                            mostrar = false;
                        }
                    }
                    
                    fila.style.display = mostrar ? '' : 'none';
                });
            }
        </script>
    @endpush
</x-app-layout>