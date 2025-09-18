<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Pagos de Suscripción') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        Pagos - {{ $suscripcion->cliente->user->nombre }} 
                        ({{ $suscripcion->tipo }})
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.suscripciones.pagos.registrar', $suscripcion) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Monto *</label>
                                <input type="number" step="0.01" class="form-control" name="monto" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Período *</label>
                                <input type="text" class="form-control" name="periodo" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Método de Pago *</label>
                                <input type="text" class="form-control" name="metodo_pago" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar Pago
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Período</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Fecha Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suscripcion->pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->periodo }}</td>
                                        <td>Bs./ {{ number_format($pago->monto, 2) }}</td>
                                        <td>{{ $pago->metodo_pago }}</td>
                                        <td>
                                            <span class="badge bg-{{ $pago->estado == 'pagado' ? 'success' : 'warning' }}">
                                                {{ ucfirst($pago->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $pago->fecha_pago?->format('d/m/Y') ?? 'Pendiente' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.suscripciones') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Suscripciones
            </a>
        </div>
    </div>
</x-app-layout>