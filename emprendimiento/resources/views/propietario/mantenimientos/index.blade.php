<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Mantenimientos Programados') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Mantenimientos</h5>
                    <a href="{{ route('propietario.mantenimientos.crear') }}" class="btn btn-dark btn-sm">
                        <i class="bi bi-tools me-1"></i>Nuevo Mantenimiento
                    </a>
                </div>
                <div class="card-body">
                    @if($mantenimientos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Medidor</th>
                                        <th>Tipo</th>
                                        <th>Costo</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mantenimientos as $mantenimiento)
                                        <tr>
                                            <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                                            <td>{{ $mantenimiento->medidor->codigo_lorawan }}</td>
                                            <td>
                                                <span class="badge bg-primary text-capitalize">
                                                    {{ $mantenimiento->tipo }}
                                                </span>
                                            </td>
                                            <td>Bs./ {{ number_format($mantenimiento->costo, 2) }}</td>
                                            <td>{{ Str::limit($mantenimiento->descripcion, 50) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay mantenimientos programados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>