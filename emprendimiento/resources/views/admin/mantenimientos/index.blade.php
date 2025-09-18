<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Mantenimientos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Mantenimientos Programados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Medidor</th>
                                    <th>Edificio</th>
                                    <th>Tipo</th>
                                    <th>Costo</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mantenimientos as $mantenimiento)
                                    <tr>
                                        <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $mantenimiento->medidor->codigo_lorawan }}</td>
                                        <td>{{ $mantenimiento->medidor->departamento->edificio->nombre }}</td>
                                        <td>
                                            <span class="badge bg-primary text-capitalize">
                                                {{ $mantenimiento->tipo }}
                                            </span>
                                        </td>
                                        <td>Bs./ {{ number_format($mantenimiento->costo, 2) }}</td>
                                        <td>{{ Str::limit($mantenimiento->descripcion, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $mantenimiento->completado ? 'success' : 'warning' }}">
                                                {{ $mantenimiento->completado ? 'Completado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(!$mantenimiento->completado)
                                                <form action="{{ route('admin.mantenimientos.completar', $mantenimiento) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle me-1"></i>Completar
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $mantenimientos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>