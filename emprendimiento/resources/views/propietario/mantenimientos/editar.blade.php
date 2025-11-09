<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Editar Mantenimiento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Editar Mantenimiento</h5>
                </div>
                <div class="card-body">
                    <!-- Información del Mantenimiento Actual -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Mantenimiento Actual</h6>
                        <p class="mb-1"><strong>Medidor:</strong> {{ $mantenimiento->medidor->codigo_lorawan }}</p>
                        <p class="mb-1"><strong>Edificio:</strong> {{ $mantenimiento->medidor->departamento->edificio->nombre }}</p>
                        <p class="mb-1"><strong>Departamento:</strong> {{ $mantenimiento->medidor->departamento->numero_departamento }}</p>
                    </div>

                    <form action="{{ route('propietario.mantenimientos.actualizar', $mantenimiento) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Tipo de Mantenimiento -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Mantenimiento *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="preventivo" {{ $mantenimiento->tipo == 'preventivo' ? 'selected' : '' }}>Preventivo</option>
                                    <option value="correctivo" {{ $mantenimiento->tipo == 'correctivo' ? 'selected' : '' }}>Correctivo</option>
                                    <option value="calibracion"{{ $mantenimiento->tipo == 'calibracion' ? 'selected' : '' }}>Calibración</option>
                                </select>
                            </div>
                            <!-- Fecha -->
                            <div class="col-md-6">
                                <label class="form-label">Fecha del Mantenimiento *</label>
                                <input type="date" class="form-control" name="fecha" 
                                       value="{{ old('fecha', $mantenimiento->fecha->format('Y-m-d')) }}" required>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label class="form-label">Descripción del Mantenimiento *</label>
                                <textarea class="form-control" name="descripcion" rows="4" required>{{ old('descripcion', $mantenimiento->descripcion) }}</textarea>
                                <small class="form-text text-muted">
                                    Máximo 200 caracteres ({{ strlen($mantenimiento->descripcion) }}/200)
                                </small>
                            </div>

                            <!-- Botones -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Actualizar Mantenimiento
                                </button>
                                <a href="{{ route('propietario.mantenimientos') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>