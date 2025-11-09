<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Editar Medidor') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Editar Medidor: {{ $medidor->codigo_lorawan }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.medidores.actualizar', $medidor) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código LoRaWAN *</label>
                                <input type="text" class="form-control" name="codigo_lorawan" value="{{ $medidor->codigo_lorawan }}" required>
                                <small class="text-muted">Código único del medidor</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departamento *</label>
                                <select class="form-select" name="id_departamento" required>
                                    <option value="">Seleccionar departamento</option>
                                    @foreach($departamentos as $departamento)
                                        <option value="{{ $departamento->id }}" 
                                            {{ $medidor->id_departamento == $departamento->id ? 'selected' : '' }}>
                                            {{ $departamento->edificio->nombre }} - {{ $departamento->numero_departamento }} (Piso {{ $departamento->piso }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gateway</label>
                                <select class="form-select" name="id_gateway">
                                    <option value="">Sin gateway asignado</option>
                                    @foreach($gateways as $gateway)
                                        <option value="{{ $gateway->id }}" 
                                            {{ $medidor->id_gateway == $gateway->id ? 'selected' : '' }}>
                                            {{ $gateway->codigo_gateway }} - {{ $gateway->ubicacion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado *</label>
                                <select class="form-select" name="estado" required>
                                    <option value="activo" {{ $medidor->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ $medidor->estado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Instalación *</label>
                                <input type="date" class="form-control" name="fecha_instalacion" value="{{ $medidor->fecha_instalacion?->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Actualizar Medidor
                                </button>
                                <a href="{{ route('admin.medidores') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Sección de Eliminación -->
                    <hr>
                    <div class="mt-4">
                        <h6 class="text-danger">Zona de Peligro</h6>
                        <p class="text-muted small">
                            @if($medidor->consumos()->count() > 0 || $medidor->alertas()->count() > 0 || $medidor->mantenimientos()->count() > 0)
                                Este medidor no puede ser eliminado porque tiene:
                                <ul class="small">
                                    @if($medidor->consumos()->count() > 0)
                                        <li>{{ $medidor->consumos()->count() }} registros de consumo</li>
                                    @endif
                                    @if($medidor->alertas()->count() > 0)
                                        <li>{{ $medidor->alertas()->count() }} alertas registradas</li>
                                    @endif
                                    @if($medidor->mantenimientos()->count() > 0)
                                        <li>{{ $medidor->mantenimientos()->count() }} mantenimientos registrados</li>
                                    @endif
                                </ul>
                            @else
                                Una vez eliminado, no podrá recuperarse.
                            @endif
                        </p>
                        
                        @if($medidor->consumos()->count() == 0 && $medidor->alertas()->count() == 0 && $medidor->mantenimientos()->count() == 0)
                            <form action="{{ route('admin.medidores.eliminar', $medidor) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('¿Estás seguro de eliminar este medidor? Esta acción no se puede deshacer.')">
                                    <i class="bi bi-trash me-1"></i>Eliminar Medidor
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>