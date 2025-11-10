<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Gateways - {{ now()->format('d/m/Y') }}</title>
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
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .summary { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Gateways</h1>
        <h3>Sistema de Gestión de Agua</h3>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%; text-align: center;">
                    <strong>Total Gateways</strong><br>
                    {{ $gateways->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Total Medidores</strong><br>
                    {{ $gateways->sum('medidores_count') }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Promedio por Gateway</strong><br>
                    {{ $gateways->count() > 0 ? number_format($gateways->sum('medidores_count') / $gateways->count(), 1) : 0 }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Sin Medidores</strong><br>
                    {{ $gateways->where('medidores_count', 0)->count() }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Gateways -->
    @if($gateways->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Código Gateway</th>
                    <th>Descripción</th>
                    <th>Ubicación</th>
                    <th>Medidores Asociados</th>
                    <th>Fecha Creación</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gateways as $gateway)
                    <tr>
                        <td><strong>{{ $gateway->codigo_gateway }}</strong></td>
                        <td>{{ $gateway->descripcion ?? 'N/A' }}</td>
                        <td>{{ $gateway->ubicacion ?? 'N/A' }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-{{ $gateway->medidores_count > 0 ? 'success' : 'warning' }}">
                                {{ $gateway->medidores_count }}
                            </span>
                        </td>
                        <td>{{ $gateway->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge badge-{{ $gateway->medidores_count > 0 ? 'success' : 'info' }}">
                                {{ $gateway->medidores_count > 0 ? 'En Uso' : 'Disponible' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Detalle de medidores por gateway -->
        <div style="margin-top: 30px;">
            <h4>Detalle de Medidores por Gateway</h4>
            @foreach($gateways->where('medidores_count', '>', 0) as $gateway)
                <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 10px;">
                    <h5 style="margin-bottom: 10px;">
                        Gateway: {{ $gateway->codigo_gateway }} 
                        <span class="badge badge-info">{{ $gateway->medidores_count }} medidores</span>
                    </h5>
                    
                    @if($gateway->medidores->count() > 0)
                        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="border: 1px solid #ddd; padding: 5px;">Medidor</th>
                                    <th style="border: 1px solid #ddd; padding: 5px;">Departamento</th>
                                    <th style="border: 1px solid #ddd; padding: 5px;">Edificio</th>
                                    <th style="border: 1px solid #ddd; padding: 5px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gateway->medidores as $medidor)
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 5px;">{{ $medidor->codigo_lorawan }}</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            {{ $medidor->departamento->numero_departamento }}
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            {{ $medidor->departamento->edificio->nombre }}
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            <span class="badge badge-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                                {{ ucfirst($medidor->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; color: #666; margin: 40px 0;">
            <h4>No hay gateways registrados en el sistema</h4>
            <p>No se encontraron registros de gateways.</p>
        </div>
    @endif
</body>
</html>