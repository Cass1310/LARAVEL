<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Alertas de Mis Edificios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @forelse($alertas->groupBy(fn($a) => $a->medidor->departamento->edificio->nombre) as $edificio => $alertasEdificio)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">{{ $edificio }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Departamento</th>
                                        <th>Medidor</th>
                                        <th>Tipo</th>
                                        <th>Valor Detectado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alertasEdificio as $alerta)
                                        <tr>
                                            <td>{{ $alerta->fecha_hora->format('d/m/Y H:i') }}</td>
                                            <td>{{ $alerta->medidor->departamento->numero_departamento }}</td>
                                            <td>{{ $alerta->medidor->codigo_lorawan }}</td>
                                            <td>
                                                @switch($alerta->tipo_alerta)
                                                    @case('fuga')
                                                        <span class="badge bg-danger">Fuga</span>
                                                        @break
                                                    @case('consumo_brusco')
                                                        <span class="badge bg-warning text-dark">Consumo Brusco</span>
                                                        @break
                                                    @case('consumo_excesivo')
                                                        <span class="badge bg-info">Consumo Excesivo</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ number_format($alerta->valor_detectado, 2) }} m³</td>
                                            <td>
                                                <span class="badge bg-{{ $alerta->estado == 'pendiente' ? 'warning' : ($alerta->estado == 'atendida' ? 'info' : 'success') }}">
                                                    {{ ucfirst($alerta->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    No se encontraron alertas en tus edificios.
                </div>
            @endforelse

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-4">
                {{ $alertas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
