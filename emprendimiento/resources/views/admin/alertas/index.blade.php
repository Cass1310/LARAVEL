<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-semibold text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Alertas') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Historial de Alertas</h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" onchange="filtrarAlertas()" id="filtroTipo">
                                <option value="">Todos</option>
                                <option value="fuga">Fuga</option>
                                <option value="consumo_brusco">Consumo Brusco</option>
                                <option value="consumo_excesivo">Consumo Excesivo</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" onchange="filtrarAlertas()" id="filtroEstado">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="resuelta">Resuelta</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" onchange="filtrarAlertas()" id="filtroEdificio">
                                <option value="">Todos</option>
                                @foreach($alertas->pluck('medidor.departamento.edificio.nombre')->unique() as $edificio)
                                    <option value="{{ $edificio }}">{{ $edificio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" onchange="filtrarAlertas()" id="filtroFechaDesde">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaAlertas">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Medidor</th>
                                    <th>Departamento</th>
                                    <th>Edificio</th>
                                    <th>Tipo</th>
                                    <th>Valor Detectado</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alertas as $alerta)
                                    <tr data-tipo="{{ $alerta->tipo_alerta }}" data-estado="{{ $alerta->estado }}" data-edificio="{{ $alerta->medidor->departamento->edificio->nombre }}" data-fecha="{{ $alerta->fecha_hora->format('Y-m-d') }}">
                                        <td>{{ $alerta->fecha_hora->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $alerta->medidor->codigo_lorawan }}</strong>
                                        </td>
                                        <td>{{ $alerta->medidor->departamento->numero_departamento }}</td>
                                        <td>{{ $alerta->medidor->departamento->edificio->nombre }}</td>
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
                                        <td>
                                            @if($alerta->estado == 'pendiente')
                                                <form action="{{ route('admin.alertas.atender', $alerta) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check-circle me-1"></i>Resolver
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Resuelta</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function filtrarAlertas() {
                const tipo = document.getElementById('filtroTipo').value;
                const estado = document.getElementById('filtroEstado').value;
                const edificio = document.getElementById('filtroEdificio').value;
                const fechaDesde = document.getElementById('filtroFechaDesde').value;
                
                const filas = document.querySelectorAll('#tablaAlertas tbody tr');
                
                filas.forEach(fila => {
                    let mostrar = true;
                    
                    if (tipo && fila.dataset.tipo !== tipo) {
                        mostrar = false;
                    }
                    
                    if (estado && fila.dataset.estado !== estado) {
                        mostrar = false;
                    }
                    
                    if (edificio && fila.dataset.edificio !== edificio) {
                        mostrar = false;
                    }
                    
                    if (fechaDesde && fila.dataset.fecha < fechaDesde) {
                        mostrar = false;
                    }
                    
                    fila.style.display = mostrar ? '' : 'none';
                });
            }
        </script>
    @endpush
</x-app-layout>