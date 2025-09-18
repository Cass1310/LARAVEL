<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Crear Nuevo Departamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Datos del Departamento</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.departamentos.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Edificio *</label>
                                <select class="form-select" name="id_edificio" required>
                                    <option value="">Seleccionar edificio</option>
                                    @foreach($edificios as $edificio)
                                        <option value="{{ $edificio->id }}">{{ $edificio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Número de Departamento *</label>
                                <input type="text" class="form-control" name="numero_departamento" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Piso *</label>
                                <input type="text" class="form-control" name="piso" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Crear Departamento
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