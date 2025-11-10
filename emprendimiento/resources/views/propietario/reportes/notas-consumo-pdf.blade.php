<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notas de Consumo - {{ $edificio->nombre }} - {{ $mes }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .table tfoot { background-color: #333; color: white; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .summary { margin-bottom: 20px; }
        .summary-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Notas de Consumo - {{ $edificio->nombre }}</h1>
        <h3>Período: {{ \Carbon\Carbon::parse($mes)->format('F Y') }}</h3>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Departamentos:</strong><br>
                    {{ count($notasConsumo) }}
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Monto:</strong><br>
                    Bs./ {{ number_format(collect($notasConsumo)->sum('monto_asignado'), 2) }}
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Consumo:</strong><br>
                    {{ number_format(collect($notasConsumo)->sum('consumo_m3'), 2) }} m³
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Pagados:</strong><br>
                    {{ collect($notasConsumo)->where('estado', 'pagado')->count() }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Tabla de Notas de Consumo -->
    @if(count($notasConsumo) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Residentes</th>
                    <th class="text-end">Consumo (m³)</th>
                    <th class="text-end">Porcentaje</th>
                    <th class="text-end">Monto (Bs.)</th>
                    <th>Estado</th>
                    <th>Vencimiento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notasConsumo as $nota)
                    <tr>
                        <td>Depto {{ $nota['departamento'] }}</td>
                        <td>{{ $nota['residentes'] }}</td>
                        <td class="text-end">{{ number_format($nota['consumo_m3'], 2) }}</td>
                        <td class="text-end">{{ number_format($nota['porcentaje_consumo'], 1) }}%</td>
                        <td class="text-end">Bs./ {{ number_format($nota['monto_asignado'], 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $nota['estado'] == 'pagado' ? 'success' : 'warning' }}">
                                {{ ucfirst($nota['estado']) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($nota['fecha_vencimiento'])->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>TOTALES</strong></td>
                    <td class="text-end"><strong>{{ number_format(collect($notasConsumo)->sum('consumo_m3'), 2) }} m³</strong></td>
                    <td class="text-end"><strong>100%</strong></td>
                    <td class="text-end"><strong>Bs./ {{ number_format(collect($notasConsumo)->sum('monto_asignado'), 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align: center; color: #666; margin: 40px 0;">
            No hay notas de consumo para el mes seleccionado.
        </p>
    @endif
</body>
</html>