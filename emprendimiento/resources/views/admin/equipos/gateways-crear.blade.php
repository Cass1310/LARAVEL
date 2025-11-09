<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Crear Gateway') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Nuevo Gateway</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.gateways.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Código Gateway *</label>
                                <input type="text" class="form-control" name="codigo_gateway" required>
                                <small class="text-muted">Código único del gateway</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" name="ubicacion" placeholder="Ej: Torre A, Piso 3">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción del gateway..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Crear Gateway
                                </button>
                                <a href="{{ route('admin.gateways') }}" class="btn btn-secondary">
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