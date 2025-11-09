<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Editar Gateway') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Editar Gateway: {{ $gateway->codigo_gateway }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.gateways.actualizar', $gateway) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código Gateway *</label>
                                <input type="text" class="form-control" name="codigo_gateway" value="{{ $gateway->codigo_gateway }}" required>
                                <small class="text-muted">Código único del gateway</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" name="ubicacion" value="{{ $gateway->ubicacion }}" placeholder="Ej: Torre A, Piso 3">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción del gateway...">{{ $gateway->descripcion }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Actualizar Gateway
                                </button>
                                <a href="{{ route('admin.gateways') }}" class="btn btn-secondary">
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
                            @if($gateway->medidores()->count() > 0)
                                Este gateway no puede ser eliminado porque tiene {{ $gateway->medidores()->count() }} medidores asociados.
                                Debes desasignar todos los medidores primero.
                            @else
                                Una vez eliminado, no podrá recuperarse.
                            @endif
                        </p>
                        
                        @if($gateway->medidores()->count() == 0)
                            <form action="{{ route('admin.gateways.eliminar', $gateway) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('¿Estás seguro de eliminar este gateway? Esta acción no se puede deshacer.')">
                                    <i class="bi bi-trash me-1"></i>Eliminar Gateway
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>