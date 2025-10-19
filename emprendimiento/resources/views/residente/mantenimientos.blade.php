<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Mantenimientos de Mi Departamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($departamento)
                <!-- Solicitar Mantenimiento -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tools me-2"></i>Solicitar Mantenimiento
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('residente.solicitar.mantenimiento') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Medidor</label>
                                    <select class="form-select" name="id_medidor" required>
                                        <option value="">Seleccionar medidor</option>
                                        @foreach($departamento->medidores as $medidor)
                                            <option value="{{ $medidor->id }}">{{ $medidor->codigo_lorawan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Mantenimiento</label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="correctivo">Correctivo</option>
                                        <option value="preventivo">Preventivo</option>
                                        <option value="calibracion">Calibración</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción del Problema</label>
                                    <textarea class="form-control" name="descripcion" rows="3" required placeholder="Describa el problema o necesidad de mantenimiento"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-send me-1"></i>Solicitar Mantenimiento
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Historial de Mantenimientos -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>Historial de Mantenimientos
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($mantenimientos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Medidor</th>
                                            <th>Tipo</th>
                                            <th>Cobertura</th>
                                            <th>Costo</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mantenimientos as $mantenimiento)
                                            <tr>
                                                <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                                                <td>{{ $mantenimiento->medidor->codigo_lorawan }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $mantenimiento->tipo == 'preventivo' ? 'info' : ($mantenimiento->tipo == 'correctivo' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($mantenimiento->tipo) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $mantenimiento->cobertura == 'incluido_suscripcion' ? 'success' : 'primary' }}">
                                                        {{ str_replace('_', ' ', ucfirst($mantenimiento->cobertura)) }}
                                                    </span>
                                                </td>
                                                <td>Bs./ {{ number_format($mantenimiento->costo, 2) }}</td>
                                                <td>{{ Str::limit($mantenimiento->descripcion, 50) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $mantenimiento->fecha->isFuture() ? 'warning' : 'success' }}">
                                                        {{ $mantenimiento->fecha->isFuture() ? 'Programado' : 'Completado' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No se encontraron mantenimientos para tu departamento.
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