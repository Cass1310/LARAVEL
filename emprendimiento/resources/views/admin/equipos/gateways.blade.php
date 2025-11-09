<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Gateways') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Gateways</h5>
                    <a href="{{ route('admin.gateways.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo Gateway
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código Gateway</th>
                                    <th>Descripción</th>
                                    <th>Ubicación</th>
                                    <th>Medidores Asociados</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gateways as $gateway)
                                    <tr>
                                        <td>
                                            <strong>{{ $gateway->codigo_gateway }}</strong>
                                        </td>
                                        <td>{{ $gateway->descripcion ?? 'N/A' }}</td>
                                        <td>{{ $gateway->ubicacion ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $gateway->medidores->count() }}</span>
                                        </td>
                                        <td>{{ $gateway->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.gateways.editar', $gateway) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                @if($gateway->medidores()->count() == 0)
                                                    <form action="{{ route('admin.gateways.eliminar', $gateway) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('¿Eliminar este gateway?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" title="No se puede eliminar - Tiene medidores asociados">
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
</x-app-layout>