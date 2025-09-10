<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Mis Suscripciones') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($suscripcionActiva)
                <!-- Suscripción Activa -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>Suscripción Activa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> 
                                    <span class="badge bg-primary text-capitalize">{{ $suscripcionActiva->tipo }}</span>
                                </p>
                                <p><strong>Precio:</strong> Bs./ {{ number_format($suscripcionActiva->monto_total, 2) }}</p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-success">{{ ucfirst($suscripcionActiva->estado) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha Inicio:</strong> {{ $suscripcionActiva->fecha_inicio->format('d/m/Y') }}</p>
                                <p><strong>Fecha Fin:</strong> {{ $suscripcionActiva->fecha_fin->format('d/m/Y') }}</p>
                                <p><strong>Días Restantes:</strong> 
                                    <span class="badge bg-{{ $suscripcionActiva->fecha_fin->diffInDays(now()) < 15 ? 'warning' : 'info' }}">
                                        {{ $suscripcionActiva->fecha_fin->diffInDays(now()) }} días
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-3">
                            @can('renovar', $suscripcionActiva)
                                <form action="{{ route('suscripcion.renovar', $suscripcionActiva) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-arrow-repeat me-1"></i>Renovar
                                    </button>
                                </form>
                            @endcan
                            @can('cancelar', $suscripcionActiva)
                                <form action="{{ route('suscripcion.cancelar', $suscripcionActiva) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('¿Estás seguro de cancelar tu suscripción?')">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @else
                <!-- Sin Suscripción Activa -->
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h4>No tienes una suscripción activa</h4>
                    <p>Para acceder a todas las funcionalidades, activa tu suscripción</p>
                    <a href="{{ route('suscripcion.crear') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-credit-card me-2"></i>Activar Suscripción
                    </a>
                </div>
            @endif

            <!-- Historial de Suscripciones -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Historial de Suscripciones</h5>
                </div>
                <div class="card-body">
                    @if($suscripciones->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Estado</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suscripciones as $suscripcion)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary text-capitalize">{{ $suscripcion->tipo }}</span>
                                            </td>
                                            <td>{{ $suscripcion->fecha_inicio->format('d/m/Y') }}</td>
                                            <td>{{ $suscripcion->fecha_fin->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $suscripcion->estado == 'activa' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($suscripcion->estado) }}
                                                </span>
                                            </td>
                                            <td>Bs./ {{ number_format($suscripcion->monto_total, 2) }}</td>
                                            <td>
                                                <a href="{{ route('suscripcion.pagos', $suscripcion) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-receipt"></i> Pagos
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay historial de suscripciones.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>