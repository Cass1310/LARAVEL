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
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-wifi me-2"></i>Medidor: {{ $medidor->codigo_lorawan }}
                                    </h6>
                                    <span class="badge bg-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                        {{ ucfirst($medidor->estado) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Device EUI:</strong> 
                                                <code>{{ $medidor->device_eui ?? 'No configurado' }}</code>
                                            </p>
                                            <p><strong>Gateway:</strong> {{ $medidor->gateway->codigo_gateway }}</p>
                                            <p><strong>Ubicación Gateway:</strong> {{ $medidor->gateway->ubicacion }}</p>
                                            <p><strong>Instalación:</strong> {{ $medidor->fecha_instalacion?->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Descripción Gateway:</strong> {{ $medidor->gateway->descripcion }}</p>
                                            @if($medidor->dev_id)
                                                <p><strong>Device ID:</strong> {{ $medidor->dev_id }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Estadísticas de Consumo -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Estadísticas del Mes Actual
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            @php
                                $mesActual = now()->format('Y-m');
                                $totalConsumoMes = 0;
                                $totalLecturas = 0;
                                
                                foreach($departamento->medidores as $medidor) {
                                    $consumosMes = $medidor->consumos()
                                        ->whereYear('fecha_hora', now()->year)
                                        ->whereMonth('fecha_hora', now()->month)
                                        ->get();
                                    
                                    $totalConsumoMes += $consumosMes->sum('consumo_intervalo_m3');
                                    $totalLecturas += $consumosMes->count();
                                }
                                
                                $consumoPromedio = $totalLecturas > 0 ? $totalConsumoMes / $totalLecturas : 0;
                            @endphp
                            
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-primary">{{ number_format($totalConsumoMes, 3) }}</h3>
                                        <p class="mb-0">m³ consumidos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-success">{{ $totalLecturas }}</h3>
                                        <p class="mb-0">Lecturas recibidas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-warning">{{ number_format($consumoPromedio * 1000, 0) }}</h3>
                                        <p class="mb-0">Litros por lectura</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-info">{{ number_format($totalConsumoMes * 1000, 0) }}</h3>
                                        <p class="mb-0">Litros totales</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Consumos con Nueva Información -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-droplet me-2"></i>Últimos Registros de Consumo - Datos en Tiempo Real
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Medidor</th>
                                        <th>Totalizador (m³)</th>
                                        <th>Consumo (m³)</th>
                                        <th>Flow (L/min)</th>
                                        <th>Batería</th>
                                        <th>Estado</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $ultimosConsumos = collect();
                                        foreach($departamento->medidores as $medidor) {
                                            $ultimosConsumos = $ultimosConsumos->merge(
                                                $medidor->consumos()->orderBy('fecha_hora', 'desc')->take(10)->get()
                                            );
                                        }
                                        $ultimosConsumos = $ultimosConsumos->sortByDesc('fecha_hora')->take(15);
                                    @endphp
                                    
                                    @foreach($ultimosConsumos as $consumo)
                                        <tr>
                                            <td>
                                                <small>{{ $consumo->fecha_hora->format('d/m/Y') }}</small><br>
                                                <strong>{{ $consumo->fecha_hora->format('H:i') }}</strong>
                                            </td>
                                            <td>
                                                <code>{{ $consumo->medidor->codigo_lorawan }}</code>
                                            </td>
                                            <td class="text-end">
                                                <strong>{{ number_format($consumo->totalizador_m3, 3) }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-{{ $consumo->consumo_intervalo_m3 > 0.050 ? 'warning' : 'success' }}">
                                                    {{ number_format((float)$consumo->consumo_intervalo_m3, 4) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($consumo->flow_l_min)
                                                    <span class="badge bg-{{ $consumo->flow_l_min > 2.0 ? 'danger' : 'info' }}">
                                                        {{ number_format((float)$consumo->flow_l_min, 1) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($consumo->bateria)
                                                    @php
                                                        $bateriaColor = match(true) {
                                                            $consumo->bateria >= 80 => 'success',
                                                            $consumo->bateria >= 50 => 'warning',
                                                            default => 'danger'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $bateriaColor }}">
                                                        {{ $consumo->bateria }}%
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($consumo->flags && ($consumo->flags['leak'] ?? false))
                                                    <span class="badge bg-danger" title="Fuga detectada">
                                                        <i class="bi bi-exclamation-triangle"></i> Fuga
                                                    </span>
                                                @elseif($consumo->flags && ($consumo->flags['backflow'] ?? false))
                                                    <span class="badge bg-warning" title="Retroflujo detectado">
                                                        <i class="bi bi-arrow-left-right"></i> Retroflujo
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">Normal</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $consumo->tipo_registro == 'transmision' ? 'primary' : 'secondary' }}">
                                                    {{ $consumo->tipo_registro == 'transmision' ? 'Auto' : 'Manual' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    @if($ultimosConsumos->isEmpty())
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox me-2"></i>No hay registros de consumo disponibles
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Leyenda -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Leyenda:</strong>
                                <span class="badge bg-success ms-2">Normal</span>
                                <span class="badge bg-warning ms-1">Consumo Alto</span>
                                <span class="badge bg-danger ms-1">Fuga/Problema</span>
                                <span class="badge bg-info ms-1">Flow Activo</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Alertas Activas -->
                @php
                    $alertasActivas = \App\Models\Alerta::whereHas('medidor.departamento', function($query) use ($departamento) {
                        $query->where('id', $departamento->id);
                    })
                    ->where('estado', 'pendiente')
                    ->with('medidor')
                    ->orderBy('fecha_hora', 'desc')
                    ->get();
                @endphp

                <!-- En la sección de Alertas Activas -->
                @if($alertasActivas->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Alertas Activas
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($alertasActivas as $alerta)
                            <div class="alert alert-{{ $alerta->tipo_alerta == 'fuga' ? 'danger' : 'warning' }} d-flex align-items-center">
                                <i class="bi bi-{{ $alerta->tipo_alerta == 'fuga' ? 'exclamation-triangle' : 'lightning' }} me-3 fs-4"></i>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">
                                        Alerta de {{ str_replace('_', ' ', $alerta->tipo_alerta) }}
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Medidor:</strong> {{ $alerta->medidor->codigo_lorawan }} | 
                                        <strong>Detectado:</strong> {{ $alerta->fecha_hora->format('d/m/Y H:i') }}
                                    </p>
                                    @if($alerta->valor_detectado)
                                        <small>
                                            Valor detectado: 
                                            @if($alerta->tipo_alerta == 'fuga')
                                                {{ number_format((float)$alerta->valor_detectado, 2) }} L/min
                                            @else
                                                {{ number_format((float)$alerta->valor_detectado, 4) }} m³
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            @else
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
                        <div>
                            <h5 class="alert-heading">Departamento no asignado</h5>
                            <p class="mb-0">No tienes un departamento asignado actualmente. Contacta al administrador del sistema.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .table th {
            border-top: none;
            font-weight: 600;
        }
        .badge {
            font-size: 0.75em;
        }
        code {
            font-size: 0.875em;
            background-color: #f8f9fa;
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
        }
    </style>
    @endpush
</x-app-layout>