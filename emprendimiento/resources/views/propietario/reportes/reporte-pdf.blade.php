<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Consumo {{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; margin-bottom: 10px; }
        .info-section { margin: 20px 0; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #2c3e50; border-left: 4px solid #3498db; padding-left: 10px; }
        .summary-cards { display: flex; justify-content: space-between; margin: 20px 0; }
        .card { flex: 1; padding: 15px; margin: 0 5px; border-radius: 8px; text-align: center; }
        .card-consumo { background-color: #d1ecf1; border: 1px solid #bee5eb; }
        .card-alertas { background-color: #fff3cd; border: 1px solid #ffeaa7; }
        .card-facturacion { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .card-value { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .card-label { font-size: 12px; color: #666; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .table-totals { background-color: #e9ecef; font-weight: bold; }
        .progress { background-color: #e9ecef; border-radius: 4px; height: 20px; margin: 5px 0; }
        .progress-bar { height: 100%; border-radius: 4px; text-align: center; color: white; font-size: 10px; line-height: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REPORTE DE CONSUMO Y FACTURACIÓN {{ $year }}</div>
        <div class="subtitle">
            @if($edificio)
                Edificio: {{ $edificio->nombre }}
            @else
                Todos los Edificios
            @endif
        </div>
        <div style="font-size: 12px;">
            Propietario: {{ $user->nombre }} | Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Resumen General -->
    <div class="info-section">
        <div class="section-title">RESUMEN GENERAL</div>
        <div class="summary-cards">
            <div class="card card-consumo">
                <div class="card-value">{{ number_format(array_sum($consumoData), 2) }} m³</div>
                <div class="card-label">CONSUMO TOTAL</div>
            </div>
            <div class="card card-alertas">
                <div class="card-value">{{ array_sum($alertasData) }}</div>
                <div class="card-label">TOTAL ALERTAS</div>
            </div>
            <div class="card card-facturacion">
                <div class="card-value">Bs./ {{ number_format(array_sum($consumosData), 2) }}</div>
                <div class="card-label">FACTURACIÓN TOTAL</div>
            </div>
        </div>
    </div>

    <!-- Detalle Mensual -->
    <div class="info-section">
        <div class="section-title">DETALLE MENSUAL</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Consumo (m³)</th>
                    <th>% del Total</th>
                    <th>Alertas</th>
                    <th>Facturación (Bs./)</th>
                    <th>% del Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    $totalConsumo = array_sum($consumoData);
                    $totalFacturacion = array_sum($consumosData);
                @endphp
                @for($i = 1; $i <= 12; $i++)
                    @if(isset($consumoData[$i]) || isset($consumosData[$i]))
                        <tr>
                            <td>{{ $meses[$i-1] }}</td>
                            <td>{{ number_format($consumoData[$i] ?? 0, 2) }}</td>
                            <td>
                                @if($totalConsumo > 0)
                                    @php $porcentaje = (($consumoData[$i] ?? 0) / $totalConsumo) * 100; @endphp
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $porcentaje }}%; background-color: #3498db;">
                                            {{ round($porcentaje, 1) }}%
                                        </div>
                                    </div>
                                @else
                                    0%
                                @endif
                            </td>
                            <td>{{ $alertasData[$i] ?? 0 }}</td>
                            <td>Bs./ {{ number_format($consumosData[$i] ?? 0, 2) }}</td>
                            <td>
                                @if($totalFacturacion > 0)
                                    @php $porcentaje = (($consumosData[$i] ?? 0) / $totalFacturacion) * 100; @endphp
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $porcentaje }}%; background-color: #27ae60;">
                                            {{ round($porcentaje, 1) }}%
                                        </div>
                                    </div>
                                @else
                                    0%
                                @endif
                            </td>
                        </tr>
                    @endif
                @endfor
                <tr class="table-totals">
                    <td><strong>TOTAL</strong></td>
                    <td><strong>{{ number_format($totalConsumo, 2) }}</strong></td>
                    <td><strong>100%</strong></td>
                    <td><strong>{{ array_sum($alertasData) }}</strong></td>
                    <td><strong>Bs./ {{ number_format($totalFacturacion, 2) }}</strong></td>
                    <td><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Análisis Comparativo -->
    <div class="info-section">
        <div class="section-title">ANÁLISIS COMPARATIVO</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Métrica</th>
                    <th>Valor Total</th>
                    <th>Promedio Mensual</th>
                    <th>Máximo Mensual</th>
                    <th>Mínimo Mensual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Consumo (m³)</td>
                    <td>{{ number_format($totalConsumo, 2) }}</td>
                    <td>{{ number_format($totalConsumo / 12, 2) }}</td>
                    <td>{{ number_format(max($consumoData) ?? 0, 2) }}</td>
                    <td>{{ number_format(min(array_filter($consumoData)) ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Facturación (Bs./)</td>
                    <td>Bs./ {{ number_format($totalFacturacion, 2) }}</td>
                    <td>Bs./ {{ number_format($totalFacturacion / 12, 2) }}</td>
                    <td>Bs./ {{ number_format(max($consumosData) ?? 0, 2) }}</td>
                    <td>Bs./ {{ number_format(min(array_filter($consumosData)) ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente por el Sistema de Monitoreo Inteligente</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ $user->nombre }}</p>
    </div>
</body>
</html>