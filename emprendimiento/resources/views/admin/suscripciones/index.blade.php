<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Suscripciones') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Todas las Suscripciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                    <th>Monto</th>
                                    <th>Pagos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suscripciones as $suscripcion)
                                    @php
                                        $dias = (int) now()->diffInDays($suscripcion->fecha_fin, false);
                                    @endphp
                                    <tr>
                                        <td>{{ $suscripcion->cliente->user->nombre }}</td>
                                        <td>
                                            <span class="badge bg-primary text-capitalize">
                                                {{ $suscripcion->tipo }}
                                            </span>
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
                                            <span class="badge bg-info">
                                                {{ $suscripcion->pagos->count() }} pagos
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.suscripciones.pagos', $suscripcion) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-credit-card"></i> Pagos
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