<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Backups') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Disco -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Espacio Backups</h6>
                            <h4>{{ $diskSpace['backup_size'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Espacio Libre</h6>
                            <h4>{{ $diskSpace['free_space'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Espacio Total</h6>
                            <h4>{{ $diskSpace['total_space'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body py-3">
                            <h6 class="card-title">Uso del Disco</h6>
                            <h4>{{ $diskSpace['used_percentage'] }}%</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">Acciones de Backup</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <form action="{{ route('admin.backups.create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="db">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-database me-2"></i>Backup BD
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.backups.create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="full">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-archive me-2"></i>Backup Completo
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.backups.clean') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" 
                                        onclick="return confirm('¿Eliminar backups antiguos?')">
                                    <i class="bi bi-trash me-2"></i>Limpiar Backups
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Backups -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Backups Disponibles</h5>
                </div>
                <div class="card-body">
                    @if(count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                        <th>Fecha Modificación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>{{ $backup['name'] }}</td>
                                            <td>{{ $backup['size'] }}</td>
                                            <td>{{ $backup['modified'] }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.backups.download', $backup['name']) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <form action="{{ route('admin.backups.delete', $backup['name']) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('¿Eliminar este backup?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay backups disponibles.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>