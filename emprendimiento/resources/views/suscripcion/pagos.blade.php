<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Pagos de Suscripción') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        Pagos - {{ $suscripcion->tipo }} ({{ $suscripcion->fecha_inicio->format('d/m/Y') }} - {{ $suscripcion->fecha_fin->format('d/m/Y') }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($suscripcion->pagos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Fecha Pago</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suscripcion->pagos as $pago)
                                        <tr>
                                            <td>{{ $pago->periodo }}</td>
                                            <td>Bs./ {{ number_format($pago->monto, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $pago->estado == 'pagado' ? 'success' : ($pago->estado == 'pendiente' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($pago->estado) }}
                                                </span>
                                            </td>
                                            <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? 'N/A' }}</td>
                                            <td>
                                                @if($pago->estado == 'pendiente')
                                                    <form action="{{ route('suscripcion.pagos.pagar', ['suscripcion' => $suscripcion->id, 'pago' => $pago->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="bi bi-credit-card me-1"></i>Pagar
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay pagos registrados para esta suscripción.
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('suscripcion.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>