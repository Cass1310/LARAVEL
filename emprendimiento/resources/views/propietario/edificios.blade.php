<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Mis Edificios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($edificios->count() > 0)
                <div class="row">
                    @foreach($edificios as $edificio)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">{{ $edificio->nombre }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>Dirección:</strong> {{ $edificio->direccion }}</p>
                                    <p class="card-text"><strong>Departamentos:</strong> {{ $edificio->departamentos->count() }}</p>
                                    <p class="card-text"><strong>Residentes:</strong> 
                                        {{ $edificio->departamentos->sum(fn($depto) => $depto->residentes->count()) }}
                                    </p>
                                    <p class="card-text"><strong>Medidores:</strong> 
                                        {{ $edificio->departamentos->sum(fn($depto) => $depto->medidores->count()) }}
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('propietario.edificios.show', $edificio) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No tienes edificios registrados.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>