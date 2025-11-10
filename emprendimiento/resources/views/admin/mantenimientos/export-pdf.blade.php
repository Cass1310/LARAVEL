<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Mantenimientos - {{ now()->format('d/m/Y') }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        .table th { 
            background-color: #f8f9fa; 
            font-weight: bold; 
        }
        .badge { 
            padding: 4px 8px; 
            border-radius: 4px; 
            font-size: 10px; 
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .summary { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .filters {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Mantenimientos</h1>
        <h3>Sistema de Gestión de Agua</h3>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Filtros aplicados -->
    @if(!empty(array_filter($filtros)))
    <div class="filters">
        <h4>Filtros Aplicados:</h4>
        <ul>
            @if($filtros['tipo'] ?? false)
                <li><strong>Tipo:</strong> {{ ucfirst($filtros['tipo']) }}</li>
            @endif
            @if($filtros['cobertura'] ?? false)
                <li><strong>Cobertura:</strong> {{ str_replace('_', ' ', ucfirst($filtros['cobertura'])) }}</li>
            @endif
            @if($filtros['estado'] ?? false)
                <li><strong>Estado:</strong> {{ str_replace('_', ' ', ucfirst($filtros['estado'])) }}</li>
            @endif
            @if($filtros['edificio'] ?? false)
                <li><strong>Edificio:</strong> {{ $filtros['edificio'] }}</li>
            @endif
        </ul>
    </div>
    @endif

    <!-- Resumen -->
    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%; text-align: center;">
                    <strong>Total Mantenimientos</strong><br>
                    {{ $mantenimientos->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Preventivos</strong><br>
                    {{ $mantenimientos->where('tipo', 'preventivo')->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Correctivos</strong><br>
                    {{ $mantenimientos->where('tipo', 'correctivo')->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Total Costo</strong><br>
                    Bs.  {{ number_format($mantenimientos->sum('costo'), 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Mantenimientos -->
    @if($mantenimientos->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Medidor</th>
                    <th>Depto.</th>
                    <th>Edificio</th>
                    <th>Tipo/Cobertura</th>
                    <th>Costo (Bs. )</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mantenimientos as $mantenimiento)
                    <tr>
                        <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                        <td><strong>{{ $mantenimiento->medidor->codigo_lorawan }}</strong></td>
                        <td>{{ $mantenimiento->medidor->departamento->numero_departamento }}</td>
                        <td>{{ $mantenimiento->medidor->departamento->edificio->nombre }}</td>
                        <td>
                            <span class="badge badge-{{ $mantenimiento->tipo == 'preventivo' ? 'info' : ($mantenimiento->tipo == 'correctivo' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($mantenimiento->tipo) }}
                            </span><br>
                            <span class="badge badge-{{ $mantenimiento->cobertura == 'incluido_suscripcion' ? 'success' : 'primary' }}">
                                {{ str_replace('_', ' ', ucfirst($mantenimiento->cobertura)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">Bs.  {{ number_format($mantenimiento->costo, 2) }}</td>
                        <td>{{ $mantenimiento->descripcion }}</td>
                        <td>
                            <span class="badge badge-{{ 
                                $mantenimiento->estado == 'pendiente' ? 'warning' : 
                                ($mantenimiento->estado == 'en_proceso' ? 'info' : 
                                ($mantenimiento->estado == 'completado' ? 'success' : 'danger')) 
                            }}">
                                {{ str_replace('_', ' ', ucfirst($mantenimiento->estado)) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #333; color: white;">
                    <td colspan="5"><strong>TOTALES</strong></td>
                    <td style="text-align: right;"><strong>Bs.  {{ number_format($mantenimientos->sum('costo'), 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Distribución por tipo -->
        <div style="margin-top: 30px;">
            <h4>Distribución por Tipo de Mantenimiento</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 25%; text-align: center; border: 1px solid #ddd; padding: 10px;">
                        <strong>Preventivos</strong><br>
                        {{ $mantenimientos->where('tipo', 'preventivo')->count() }}<br>
                        <small>Bs.  {{ number_format($mantenimientos->where('tipo', 'preventivo')->sum('costo'), 2) }}</small>
                    </td>
                    <td style="width: 25%; text-align: center; border: 1px solid #ddd; padding: 10px;">
                        <strong>Correctivos</strong><br>
                        {{ $mantenimientos->where('tipo', 'correctivo')->count() }}<br>
                        <small>Bs.  {{ number_format($mantenimientos->where('tipo', 'correctivo')->sum('costo'), 2) }}</small>
                    </td>
                    <td style="width: 25%; text-align: center; border: 1px solid #ddd; padding: 10px;">
                        <strong>Instalaciones</strong><br>
                        {{ $mantenimientos->where('tipo', 'instalacion')->count() }}<br>
                        <small>Bs.  {{ number_format($mantenimientos->where('tipo', 'instalacion')->sum('costo'), 2) }}</small>
                    </td>
                    <td style="width: 25%; text-align: center; border: 1px solid #ddd; padding: 10px;">
                        <strong>Calibraciones</strong><br>
                        {{ $mantenimientos->where('tipo', 'calibracion')->count() }}<br>
                        <small>Bs.  {{ number_format($mantenimientos->where('tipo', 'calibracion')->sum('costo'), 2) }}</small>
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align: center; color: #666; margin: 40px 0;">
            <h4>No hay mantenimientos que coincidan con los filtros seleccionados</h4>
            <p>No se encontraron registros con los criterios de búsqueda aplicados.</p>
        </div>
    @endif
</body>
</html>