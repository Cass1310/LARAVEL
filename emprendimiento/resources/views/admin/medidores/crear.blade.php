<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Registrar Nuevo Medidor') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Datos del Medidor</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.medidores.guardar') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Departamento *</label>
                                <select class="form-select" name="id_departamento" required>
                                    <option value="">Seleccionar departamento</option>
                                    @foreach($departamentos as $departamento)
                                        <option value="{{ $departamento->id }}">
                                            {{ $departamento->edificio->nombre }} - Depto. {{ $departamento->numero_departamento }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gateway *</label>
                                <select class="form-select" name="id_gateway" required>
                                    <option value="">Seleccionar gateway</option>
                                    @foreach($gateways as $gateway)
                                        <option value="{{ $gateway->id }}">{{ $gateway->codigo_gateway }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código LoRaWAN *</label>
                                <input type="text" class="form-control" name="codigo_lorawan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Instalación *</label>
                                <input type="date" class="form-control" name="fecha_instalacion" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Registrar Medidor
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