<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Consumo {{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #666; }
        .info-section { margin: 15px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .summary { background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REPORTE ANUAL DE CONSUMO - {{ $year }}</div>
        <div class="subtitle">Departamento: {{ $departamento->numero_departamento }} - {{ $departamento->edificio->nombre }}</div>
    </div>

    <div class="info-section">
        <h3>Resumen Anual</h3>
        <div class="summary">
            <strong>Consumo Total:</strong> {{ array_sum($consumoMensual) }} m³ | 
            <strong>Total Alertas:</strong> {{ array_sum($alertasMensual) }} | 
            <strong>Total Pagado:</strong> Bs./ {{ number_format(array_sum($pagosMensual), 2) }} | 
            <strong>Promedio Mensual:</strong> Bs./ {{ number_format($promedioMensual, 2) }}
        </div>
    </div>

    <div class="info-section">
        <h3>Consumo Mensual (m³)</h3>
        <table class="table">
            <tr>
                @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                    <th>{{ $mes }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($consumoMensual as $consumo)
                    <td>{{ number_format($consumo, 2) }}</td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>Alertas Mensuales</h3>
        <table class="table">
            <tr>
                @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                    <th>{{ $mes }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($alertasMensual as $alerta)
                    <td>{{ $alerta }}</td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>Pagos Mensuales (Bs./)</h3>
        <table class="table">
            <tr>
                @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                    <th>{{ $mes }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($pagosMensual as $pago)
                    <td>{{ number_format($pago, 2) }}</td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i') }} para {{ $user->nombre }}</p>
        <p>Sistema de Monitoreo Inteligente</p>
    </div>
</body>
</html>