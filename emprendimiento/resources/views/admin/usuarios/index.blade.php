<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Usuarios</h5>
                    <a href="{{ route('admin.usuarios.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Nuevo Usuario
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Teléfono</th>
                                    <th>Creado por</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                    <tr>
                                        <td>{{ $usuario->nombre }}</td>
                                        <td>{{ $usuario->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $usuario->rol == 'administrador' ? 'danger' : ($usuario->rol == 'propietario' ? 'primary' : 'success') }}">
                                                {{ ucfirst($usuario->rol) }}
                                            </span>
                                        </td>
                                        <td>{{ $usuario->telefono ?? 'N/A' }}</td>
                                        <td>{{ $usuario->creador->nombre ?? 'Sistema' }}</td>
                                        <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.usuarios.editar', $usuario) }}" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.usuarios.eliminar', $usuario) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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