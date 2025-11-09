<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Equipos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="row">
                <!-- Resumen de Equipos -->
                <div class="col-md-3 mb-4">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 fs-1"></i>
                            <h5 class="card-title mt-2">{{ $medidores->count() }}</h5>
                            <p class="card-text small">Total Medidores</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <i class="bi bi-wifi fs-1"></i>
                            <h5 class="card-title mt-2">{{ $gateways->count() }}</h5>
                            <p class="card-text small">Total Gateways</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <i class="bi bi-check-circle fs-1"></i>
                            <h5 class="card-title mt-2">{{ $medidores->where('estado', 'activo')->count() }}</h5>
                            <p class="card-text small">Medidores Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                            <h5 class="card-title mt-2">{{ $medidores->where('estado', 'inactivo')->count() }}</h5>
                            <p class="card-text small">Medidores Inactivos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <a href="{{ route('admin.medidores') }}" class="card text-center text-decoration-none h-100">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                            <h5 class="card-title mt-2">Gestión de Medidores</h5>
                            <p class="card-text">Administrar todos los medidores del sistema</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('admin.gateways') }}" class="card text-center text-decoration-none h-100">
                        <div class="card-body">
                            <i class="bi bi-wifi fs-1 text-success"></i>
                            <h5 class="card-title mt-2">Gestión de Gateways</h5>
                            <p class="card-text">Administrar gateways LoRaWAN</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Medidores Sin Gateway -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Medidores Sin Gateway Asignado
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $medidoresSinGateway = $medidores->where('id_gateway', null);
                    @endphp
                    
                    @if($medidoresSinGateway->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Código LoRaWAN</th>
                                        <th>Departamento</th>
                                        <th>Edificio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medidoresSinGateway as $medidor)
                                        <tr>
                                            <td>{{ $medidor->codigo_lorawan }}</td>
                                            <td>{{ $medidor->departamento->numero_departamento }}</td>
                                            <td>{{ $medidor->departamento->edificio->nombre }}</td>
                                            <td>
                                                <span class="badge bg-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($medidor->estado) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#asignarGatewayModal{{ $medidor->id }}">
                                                    <i class="bi bi-wifi me-1"></i>Asignar Gateway
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Asignar Gateway -->
                                        <div class="modal fade" id="asignarGatewayModal{{ $medidor->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Asignar Gateway a {{ $medidor->codigo_lorawan }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.medidores.asignar-gateway', $medidor) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Seleccionar Gateway</label>
                                                                <select class="form-select" name="id_gateway" required>
                                                                    <option value="">Seleccionar gateway</option>
                                                                    @foreach($gateways as $gateway)
                                                                        <option value="{{ $gateway->id }}">
                                                                            {{ $gateway->codigo_gateway }} - {{ $gateway->ubicacion }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
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
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Todos los medidores tienen gateway asignado.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>