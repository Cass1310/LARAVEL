<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Facturas de Edificios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Facturas</h5>
                    <a href="{{ route('propietario.facturas.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Factura
                    </a>
                </div>
                <div class="card-body">
                    @if($facturas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Edificio</th>
                                        <th>Período</th>
                                        <th>Monto Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($facturas as $factura)
                                        <tr>
                                            <td>{{ $factura->edificio->nombre }}</td>
                                            <td>{{ $factura->periodo }}</td>
                                            <td>Bs./ {{ number_format($factura->monto_total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $factura->estado == 'pagada' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($factura->estado) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                @if($factura->estado == 'pendiente')
                                                    <form action="{{ route('propietario.facturas.pagar', $factura) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="bi bi-check-circle"></i> Pagar
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
                            No hay facturas registradas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>