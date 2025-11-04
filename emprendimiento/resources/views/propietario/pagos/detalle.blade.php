<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Detalle de Consumo') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        Consumo: {{ $consumo->edificio->nombre }} - {{ $consumo->periodo }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Información General -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <p><strong>Edificio:</strong> {{ $consumo->edificio->nombre }}</p>
                            <p><strong>Período:</strong> {{ $consumo->periodo }}</p>
                            <p><strong>Fecha Emisión:</strong> {{ $consumo->fecha_emision->format('d/m/Y') }}</p>
                            <p><strong>Vencimiento:</strong> {{ $consumo->fecha_vencimiento->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Estado y Montos</h6>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-{{ $consumo->estado == 'pagada' ? 'success' : 'warning' }}">
                                    {{ ucfirst($consumo->estado) }}
                                </span>
                            </p>
                            <p><strong>Monto Total:</strong> 
                                <span class="fs-5 text-success">
                                    Bs./ {{ number_format($consumo->monto_total, 2) }}
                                </span>
                            </p>
                            <p><strong>Fecha Pago:</strong> 
                                {{ $consumo->fecha_pago?->format('d/m/Y') ?? 'Pendiente' }}
                            </p>
                        </div>
                    </div>

                    <!-- Detalle por Departamentos -->
                    <h6 class="mb-3">Distribución por Departamentos</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Residentes</th>
                                    <th>Consumo (m³)</th>
                                    <th>Porcentaje</th>
                                    <th>Monto Asignado</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consumo->consumosDepartamento as $consumoDepto)
                                    <tr>
                                        <td>
                                            {{ $consumoDepto->departamento->numero_departamento }}
                                            <br>
                                            <small class="text-muted">Piso {{ $consumoDepto->departamento->piso }}</small>
                                        </td>
                                        <td>
                                            @foreach($consumoDepto->departamento->residentes as $residente)
                                                <span class="badge bg-light text-dark mb-1">
                                                    {{ $residente->nombre }}
                                                </span><br>
                                            @endforeach
                                        </td>
                                        <td>{{ number_format($consumoDepto->consumo_m3, 2) }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ number_format($consumoDepto->porcentaje_consumo, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <strong>Bs./ {{ number_format($consumoDepto->monto_asignado, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $consumoDepto->estado == 'pagado' ? 'success' : 'warning' }}">
                                                {{ ucfirst($consumoDepto->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <td colspan="2"><strong>TOTAL</strong></td>
                                    <td><strong>{{ number_format($consumo->consumosDepartamento->sum('consumo_m3'), 2) }}</strong></td>
                                    <td><strong>100%</strong></td>
                                    <td><strong>Bs./ {{ number_format($consumo->monto_total, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-4">
                        @if($consumo->estado == 'pendiente')
                            <a href="{{ route('propietario.pagos.mostrar', $consumo) }}" 
                               class="btn btn-success me-2">
                                <i class="bi bi-credit-card me-1"></i>Pagar Ahora
                            </a>
                        @endif
                        <a href="{{ route('propietario.pagos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver a Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>