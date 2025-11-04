<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Procesar Pago') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-credit-card me-2"></i>Pagar Consumo - {{ $consumo->edificio->nombre }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Resumen del Consumo -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Consumo</h6>
                            <p><strong>Edificio:</strong> {{ $consumo->edificio->nombre }}</p>
                            <p><strong>Período:</strong> {{ $consumo->periodo }}</p>
                            <p><strong>Fecha Emisión:</strong> {{ $consumo->fecha_emision->format('d/m/Y') }}</p>
                            <p><strong>Vencimiento:</strong> {{ $consumo->fecha_vencimiento->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Detalles de Pago</h6>
                            <p><strong>Monto Total:</strong> 
                                <span class="fs-4 text-success">
                                    Bs./ {{ number_format($consumo->monto_total, 2) }}
                                </span>
                            </p>
                            <p><strong>Departamentos:</strong> {{ $consumo->consumosDepartamento->count() }}</p>
                            <p><strong>Estado Actual:</strong> 
                                <span class="badge bg-warning">{{ ucfirst($consumo->estado) }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Formulario de Pago -->
                    <form action="{{ route('propietario.pagos.procesar', $consumo) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Método de Pago *</label>
                                <select class="form-select" name="metodo_pago" required>
                                    <option value="">Seleccionar método</option>
                                    <option value="transferencia">Transferencia Bancaria</option>
                                    <option value="deposito">Depósito</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Pago *</label>
                                <input type="date" class="form-control" name="fecha_pago" 
                                       value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Importante:</strong> Al procesar este pago, se marcarán como pagados todos los consumos de los departamentos asociados a este edificio.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Confirmar Pago
                                </button>
                                <a href="{{ route('propietario.pagos.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>