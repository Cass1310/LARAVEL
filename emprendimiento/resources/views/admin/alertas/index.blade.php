<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Alertas') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">Alertas del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Medidor</th>
                                    <th>Edificio</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alertas as $alerta)
                                    <tr>
                                        <td>{{ $alerta->fecha_hora->format('d/m/Y H:i') }}</td>
                                        <td>{{ $alerta->medidor->codigo_lorawan }}</td>
                                        <td>{{ $alerta->medidor->departamento->edificio->nombre }}</td>
                                        <td>
                                            <span class="badge bg-{{ $alerta->tipo_alerta == 'fuga' ? 'danger' : ($alerta->tipo_alerta == 'consumo_brusco' ? 'warning' : 'info') }}">
                                                {{ str_replace('_', ' ', ucfirst($alerta->tipo_alerta)) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($alerta->valor_detectado, 2) }} m³</td>
                                        <td>
                                            <span class="badge bg-{{ $alerta->estado == 'pendiente' ? 'warning' : 'success' }}">
                                                {{ ucfirst($alerta->estado) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($alerta->estado == 'pendiente')
                                                <form action="{{ route('admin.alertas.resolver', $alerta) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle me-1"></i>Resolver
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $alertas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>