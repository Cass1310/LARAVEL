<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Edificios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Edificios</h5>
                    <a href="{{ route('admin.edificios.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-building-add me-1"></i>Nuevo Edificio
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Propietario</th>
                                    <th>Departamentos</th>
                                    <th>Residentes</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($edificios as $edificio)
                                    <tr>
                                        <td>{{ $edificio->nombre }}</td>
                                        <td>{{ $edificio->direccion }}</td>
                                        <td>{{ $edificio->propietario->nombre }}</td>
                                        <td>{{ $edificio->departamentos->count() }}</td>
                                        <td>{{ $edificio->departamentos->sum(fn($depto) => $depto->residentes->count()) }}</td>
                                        <td>{{ $edificio->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
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