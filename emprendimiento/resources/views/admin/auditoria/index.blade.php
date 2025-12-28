<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Auditoría del Sistema') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Total</h6>
                            <h4>{{ $estadisticas['total'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Logins</h6>
                            <h4>{{ $estadisticas['logins'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Creaciones</h6>
                            <h4>{{ $estadisticas['creaciones'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body py-3">
                            <h6 class="card-title">Actualizaciones</h6>
                            <h4>{{ $estadisticas['actualizaciones'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-3">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body py-3">
                            <h6 class="card-title">Eliminaciones</h6>
                            <h4>{{ $estadisticas['eliminaciones'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Acción</label>
                            <select class="form-select" name="accion">
                                <option value="">Todas</option>
                                <option value="login" {{ request('accion') == 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('accion') == 'logout' ? 'selected' : '' }}>Logout</option>
                                <option value="crear" {{ request('accion') == 'crear' ? 'selected' : '' }}>Crear</option>
                                <option value="actualizar" {{ request('accion') == 'actualizar' ? 'selected' : '' }}>Actualizar</option>
                                <option value="eliminar" {{ request('accion') == 'eliminar' ? 'selected' : '' }}>Eliminar</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Módulo</label>
                            <select class="form-select" name="modulo">
                                <option value="">Todos</option>
                                <option value="User" {{ request('modulo') == 'User' ? 'selected' : '' }}>Usuarios</option>
                                <option value="Edificio" {{ request('modulo') == 'Edificio' ? 'selected' : '' }}>Edificios</option>
                                <option value="Departamento" {{ request('modulo') == 'Departamento' ? 'selected' : '' }}>Departamentos</option>
                                <option value="Medidor" {{ request('modulo') == 'Medidor' ? 'selected' : '' }}>Medidores</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" name="fecha_fin" value="{{ request('fecha_fin') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="{{ route('admin.auditoria.index') }}" class="btn btn-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Logs -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">Registros de Auditoría</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Acción</th>
                                    <th>Módulo</th>
                                    <th>Descripción</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($log->user_id)
                                                <a href="{{ route('admin.auditoria.usuario', $log->user_id) }}" 
                                                   class="text-decoration-none">
                                                    {{ $log->user_nombre }}
                                                </a>
                                            @else
                                                {{ $log->user_nombre }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $log->user_rol }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $log->accion == 'login' ? 'success' : 
                                                ($log->accion == 'logout' ? 'info' : 
                                                ($log->accion == 'crear' ? 'primary' : 
                                                ($log->accion == 'actualizar' ? 'warning' : 'danger'))) 
                                            }}">
                                                {{ ucfirst($log->accion) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->modulo ?? 'Sistema' }}</td>
                                        <td>{{ Str::limit($log->descripcion, 50) }}</td>
                                        <td><small>{{ $log->ip_address }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>