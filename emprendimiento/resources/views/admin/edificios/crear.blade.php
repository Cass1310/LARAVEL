<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Crear Nuevo Edificio') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Datos del Edificio</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.edificios.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Propietario *</label>
                                <select class="form-select" name="id_propietario" required>
                                    <option value="">Seleccionar propietario</option>
                                    @foreach($propietarios as $propietario)
                                        <option value="{{ $propietario->id }}">{{ $propietario->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Edificio *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección *</label>
                                <textarea class="form-control" name="direccion" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Crear Edificio
                                </button>
                                <a href="{{ route('admin.edificios') }}" class="btn btn-secondary">
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