<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Histórico de Notas de Consumo') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($departamento)
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Últimos 5 Meses</h5>
                        <div>
                            <a href="{{ route('residente.historico.excel') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($consumos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Consumo (m³)</th>
                                            <th>Porcentaje</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                            <th>Fecha Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consumos as $consumo)
                                            <tr>
                                                <td>{{ $consumo->consumoEdificio->periodo }}</td>
                                                <td>{{ number_format($consumo->consumo_m3, 2) }}</td>
                                                <td>{{ number_format($consumo->porcentaje_consumo, 2) }}%</td>
                                                <td>Bs./ {{ number_format($consumo->monto_asignado, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $consumo->estado == 'pagado' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($consumo->estado) }}
                                                    </span>
                                                </td>
                                                <td>{{ $consumo->fecha_pago ? $consumo->fecha_pago->format('d/m/Y') : '-' }}</td>
                                                <td>
                                                    <a href="{{ route('residente.consumo.imprimir', $consumo->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="bi bi-printer"></i>
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
                                No tiene ninguna nota de consumo en los últimos 5 meses
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No tienes un departamento asignado actualmente.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>