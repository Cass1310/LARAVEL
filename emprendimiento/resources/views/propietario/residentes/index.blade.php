<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Residentes') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Residentes Registrados</h5>
                    <a href="{{ route('propietario.residentes.crear') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Nuevo Residente
                    </a>
                </div>
                <div class="card-body">
                    @if($residentes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Departamento</th>
                                        <th>Edificio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($residentes as $residente)
                                        <tr>
                                            <td>{{ $residente->nombre }}</td>
                                            <td>{{ $residente->email }}</td>
                                            <td>{{ $residente->telefono ?? 'N/A' }}</td>
                                            <td>
                                                @foreach($residente->departamentosResidente as $departamento)
                                                    {{ $departamento->numero_departamento }}<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach($residente->departamentosResidente as $departamento)
                                                    {{ $departamento->edificio->nombre }}<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay residentes registrados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>