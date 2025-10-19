<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Consumos de Edificios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Consumos</h5>
                    <a href="{{ route('propietario.consumos.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Consumo
                    </a>
                </div>
                <div class="card-body">
                    @if($consumos->count() > 0)
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
                                    @foreach($consumos as $consumo)
                                        <tr>
                                            <td>{{ $consumo->edificio->nombre }}</td>
                                            <td>{{ $consumo->periodo }}</td>
                                            <td>Bs./ {{ number_format($consumo->monto_total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $consumo->estado == 'pagada' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($consumo->estado) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                @if($consumo->estado == 'pendiente')
                                                    <form action="{{ route('propietario.consumos.pagar', $consumo) }}" method="POST" class="d-inline">
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
                            No hay consumos registradas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>