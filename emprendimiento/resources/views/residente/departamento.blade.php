<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Mi Departamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($departamento)
                <!-- Información del Departamento -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-house-door me-2"></i>Información del Departamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Edificio:</strong> {{ $departamento->edificio->nombre }}</p>
                                <p><strong>Dirección:</strong> {{ $departamento->edificio->direccion }}</p>
                                <p><strong>Número:</strong> {{ $departamento->numero_departamento }}</p>
                                <p><strong>Piso:</strong> {{ $departamento->piso }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Propietario:</strong> {{ $departamento->edificio->propietario->nombre }}</p>
                                <p><strong>Teléfono:</strong> {{ $departamento->edificio->propietario->telefono }}</p>
                                <p><strong>Email:</strong> {{ $departamento->edificio->propietario->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medidores -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>Medidores Asociados
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($departamento->medidores as $medidor)
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">Medidor: {{ $medidor->codigo_lorawan }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Estado:</strong> 
                                                <span class="badge bg-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($medidor->estado) }}
                                                </span>
                                            </p>
                                            <p><strong>Gateway:</strong> {{ $medidor->gateway->codigo_gateway }}</p>
                                            <p><strong>Ubicación Gateway:</strong> {{ $medidor->gateway->ubicacion }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Instalación:</strong> {{ $medidor->fecha_instalacion?->format('d/m/Y') }}</p>
                                            <p><strong>Descripción Gateway:</strong> {{ $medidor->gateway->descripcion }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Últimos Consumos -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-droplet me-2"></i>Últimos Registros de Consumo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Medidor</th>
                                        <th>Volumen (m³)</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departamento->medidores as $medidor)
                                        @foreach($medidor->consumos as $consumo)
                                            <tr>
                                                <td>{{ $consumo->fecha_hora->format('d/m/Y H:i') }}</td>
                                                <td>{{ $medidor->codigo_lorawan }}</td>
                                                <td>{{ number_format($consumo->volumen, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $consumo->tipo_registro == 'transmision' ? 'primary' : 'warning' }}">
                                                        {{ ucfirst($consumo->tipo_registro) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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