<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Edificio: ') . $edificio->nombre }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Edificio -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $edificio->nombre }}</p>
                            <p><strong>Dirección:</strong> {{ $edificio->direccion }}</p>
                            <p><strong>Propietario:</strong> {{ $edificio->propietario->nombre }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Departamentos:</strong> {{ $edificio->departamentos->count() }}</p>
                            <p><strong>Total Residentes:</strong> 
                                {{ $edificio->departamentos->sum(fn($depto) => $depto->residentes->count()) }}
                            </p>
                            <p><strong>Fecha Creación:</strong> {{ $edificio->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departamentos -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Departamentos</h5>
                </div>
                <div class="card-body">
                    @if($edificio->departamentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Piso</th>
                                        <th>Residentes</th>
                                        <th>Medidores</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($edificio->departamentos as $departamento)
                                        <tr>
                                            <td>{{ $departamento->numero_departamento }}</td>
                                            <td>{{ $departamento->piso }}</td>
                                            <td>{{ $departamento->residentes->count() }}</td>
                                            <td>{{ $departamento->medidores->count() }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#deptoModal{{ $departamento->id }}">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal -->
                                        <div class="modal fade" id="deptoModal{{ $departamento->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalles Departamento</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>Residentes:</h6>
                                                        <ul>
                                                            @foreach($departamento->residentes as $residente)
                                                                <li>{{ $residente->nombre }} - {{ $residente->email }}</li>
                                                            @endforeach
                                                        </ul>
                                                        <h6>Medidores:</h6>
                                                        <ul>
                                                            @foreach($departamento->medidores as $medidor)
                                                                <li>{{ $medidor->codigo_lorawan }} - {{ $medidor->estado }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            No hay departamentos registrados en este edificio.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>