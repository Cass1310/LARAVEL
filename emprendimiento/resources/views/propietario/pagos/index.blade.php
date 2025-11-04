<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Pagos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Consumos Pendientes -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>Consumos Pendientes de Pago
                    </h5>
                </div>
                <div class="card-body">
                    @if($consumosPendientes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Edificio</th>
                                        <th>Período</th>
                                        <th>Monto Total</th>
                                        <th>Vencimiento</th>
                                        <th>Departamentos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($consumosPendientes as $consumo)
                                        <tr>
                                            <td>{{ $consumo->edificio->nombre }}</td>
                                            <td>{{ $consumo->periodo }}</td>
                                            <td>
                                                <strong class="text-success">
                                                    Bs./ {{ number_format($consumo->monto_total, 2) }}
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $consumo->fecha_vencimiento->lt(now()) ? 'danger' : 'warning' }}">
                                                    {{ $consumo->fecha_vencimiento->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $consumo->consumosDepartamento->count() }} deptos.
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('propietario.pagos.mostrar', $consumo) }}" 
                                                   class="btn btn-sm btn-success me-1">
                                                    <i class="bi bi-credit-card me-1"></i>Pagar
                                                </a>
                                                <a href="{{ route('propietario.pagos.detalle', $consumo) }}" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye me-1"></i>Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle me-2"></i>
                            No tienes consumos pendientes de pago.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Consumos Pagados -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle me-2"></i>Historial de Pagos
                    </h5>
                </div>
                <div class="card-body">
                    @if($consumosPagados->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Edificio</th>
                                        <th>Período</th>
                                        <th>Monto Total</th>
                                        <th>Fecha Pago</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($consumosPagados as $consumo)
                                        <tr>
                                            <td>{{ $consumo->edificio->nombre }}</td>
                                            <td>{{ $consumo->periodo }}</td>
                                            <td>Bs./ {{ number_format($consumo->monto_total, 2) }}</td>
                                            <td>{{ $consumo->fecha_pago?->format('d/m/Y') ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-success">Pagado</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('propietario.pagos.detalle', $consumo) }}" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye me-1"></i>Detalles
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay historial de pagos registrados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>