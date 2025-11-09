<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Propietarios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Lista de Propietarios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Edificios</th>
                                    <th>Usuarios Creados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propietarios as $propietario)
                                    <tr>
                                        <td>{{ $propietario->nombre }}</td>
                                        <td>{{ $propietario->email }}</td>
                                        <td>{{ $propietario->telefono ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $propietario->edificios_propietario_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $propietario->elementos_creados_count }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.propietarios.show', $propietario) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Ver Detalles
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