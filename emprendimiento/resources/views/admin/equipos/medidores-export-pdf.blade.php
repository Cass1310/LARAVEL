<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Medidores - {{ now()->format('d/m/Y') }}</title>
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
        .badge-danger { background-color: #dc3545; color: white; }
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
        <h1>Reporte de Medidores</h1>
        <h3>Sistema de Gestión de Agua</h3>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%; text-align: center;">
                    <strong>Total Medidores</strong><br>
                    {{ $medidores->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Activos</strong><br>
                    {{ $medidores->where('estado', 'activo')->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Inactivos</strong><br>
                    {{ $medidores->where('estado', 'inactivo')->count() }}
                </td>
                <td style="width: 25%; text-align: center;">
                    <strong>Sin Gateway</strong><br>
                    {{ $medidores->whereNull('id_gateway')->count() }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Medidores -->
    @if($medidores->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Código LoRaWAN</th>
                    <th>Departamento</th>
                    <th>Edificio</th>
                    <th>Propietario</th>
                    <th>Gateway</th>
                    <th>Estado</th>
                    <th>Fecha Instalación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medidores as $medidor)
                    <tr>
                        <td><strong>{{ $medidor->codigo_lorawan }}</strong></td>
                        <td>
                            {{ $medidor->departamento->numero_departamento }}<br>
                            <small>Piso {{ $medidor->departamento->piso }}</small>
                        </td>
                        <td>{{ $medidor->departamento->edificio->nombre }}</td>
                        <td>{{ $medidor->departamento->edificio->propietario->nombre ?? 'N/A' }}</td>
                        <td>
                            @if($medidor->gateway)
                                <span>{{ $medidor->gateway->codigo_gateway }}</span>
                            @else
                                <span class="badge badge-warning">Sin Gateway</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $medidor->estado == 'activo' ? 'success' : 'danger' }}">
                                {{ ucfirst($medidor->estado) }}
                            </span>
                        </td>
                        <td>{{ $medidor->fecha_instalacion?->format('d/m/Y') ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Distribución por edificio -->
        <div style="margin-top: 30px;">
            <h4>Distribución por Edificio</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #ddd; padding: 8px;">Edificio</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Total Medidores</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Activos</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Inactivos</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $medidoresPorEdificio = $medidores->groupBy('departamento.edificio.nombre');
                    @endphp
                    @foreach($medidoresPorEdificio as $edificio => $meds)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">{{ $edificio }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $meds->count() }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $meds->where('estado', 'activo')->count() }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $meds->where('estado', 'inactivo')->count() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="text-align: center; color: #666; margin: 40px 0;">
            <h4>No hay medidores registrados en el sistema</h4>
            <p>No se encontraron registros de medidores.</p>
        </div>
    @endif
</body>
</html>