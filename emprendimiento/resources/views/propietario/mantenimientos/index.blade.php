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
                                        <th>Edificio/Depto</th>
                                        <th>Tipo / Cobertura</th>
                                        <th>Estado</th>
                                        <th>Costo</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mantenimientos as $mantenimiento)
                                        <tr>
                                            <td>
                                                {{ $mantenimiento->fecha->format('d/m/Y') }}
                                                @if($mantenimiento->fecha->isPast())
                                                    <br><span class="badge bg-secondary">Pasado</span>
                                                @elseif($mantenimiento->fecha->isToday())
                                                    <br><span class="badge bg-warning">Hoy</span>
                                                @else
                                                    <br><span class="badge bg-success">Futuro</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $mantenimiento->medidor->codigo_lorawan }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $mantenimiento->medidor->departamento->edificio->nombre }}<br>
                                                    Depto. {{ $mantenimiento->medidor->departamento->numero_departamento }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $mantenimiento->tipo == 'preventivo' ? 'info' : ($mantenimiento->tipo == 'correctivo' ? 'warning' : 'primary') }} text-capitalize">
                                                    {{ $mantenimiento->tipo }}
                                                </span>
                                                <span class="badge bg-{{ $mantenimiento->cobertura == 'incluido_suscripcion' ? 'success' : 'secondary' }}">
                                                    {{ str_replace('_', ' ', ucfirst($mantenimiento->cobertura)) }}
                                                </span>
                                            </td>
                                            <td>
                                                    <span class="badge bg-{{ $mantenimiento->fecha->isFuture() ? 'warning' : 'success' }}">
                                                        {{ $mantenimiento->fecha->isFuture() ? 'Programado' : 'Completado' }}
                                                    </span>
                                                </td>
                                            <td>
                                                <strong>Bs./ {{ number_format($mantenimiento->costo, 2) }}</strong>
                                            </td>
                                            <td>
                                                <span data-bs-toggle="tooltip" data-bs-title="{{ $mantenimiento->descripcion }}">
                                                    {{ Str::limit($mantenimiento->descripcion, 30) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('propietario.mantenimientos.editar', $mantenimiento) }}" 
                                                       class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('propietario.mantenimientos.eliminar', $mantenimiento) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                data-bs-toggle="tooltip" title="Eliminar"
                                                                onclick="return confirm('¿Estás seguro de eliminar este mantenimiento?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $mantenimientos->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay mantenimientos programados.
                            <a href="{{ route('propietario.mantenimientos.crear') }}" class="alert-link">
                                Programar el primer mantenimiento
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Inicializar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
    @endpush
</x-app-layout>