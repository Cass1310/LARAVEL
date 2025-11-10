<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota de Consumo - {{ $consumo->consumoEdificio->periodo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #666; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .info-table .label { background-color: #f8f9fa; font-weight: bold; width: 30%; }
        .amount { font-size: 16px; font-weight: bold; color: #dc3545; text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .consumo-details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">NOTA DE CONSUMO DE AGUA</div>
        <div class="subtitle">Sistema de Monitoreo Inteligente</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Periodo:</td>
            <td>{{ $consumo->consumoEdificio->periodo }}</td>
        </tr>
        <tr>
            <td class="label">Edificio:</td>
            <td>{{ $consumo->consumoEdificio->edificio->nombre }}</td>
        </tr>
        <tr>
            <td class="label">Departamento:</td>
            <td>{{ $consumo->departamento->numero_departamento }} - Piso {{ $consumo->departamento->piso }}</td>
        </tr>
        <tr>
            <td class="label">Fecha Emisión:</td>
            <td>{{ $consumo->consumoEdificio->fecha_emision->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Fecha Vencimiento:</td>
            <td>{{ $consumo->consumoEdificio->fecha_vencimiento ? $consumo->consumoEdificio->fecha_vencimiento->format('d/m/Y') : 'No especificada' }}</td>
        </tr>
    </table>

    <div class="consumo-details">
        <h3 style="text-align: center; margin-bottom: 15px;">DETALLES DE CONSUMO</h3>
        
        <table class="info-table">
            <tr>
                <td class="label">Consumo Registrado (m³):</td>
                <td>{{ number_format($consumo->consumo_m3, 2) }} m³</td>
            </tr>
            <tr>
                <td class="label">Porcentaje del Edificio:</td>
                <td>{{ number_format($consumo->porcentaje_consumo, 2) }}%</td>
            </tr>
            <tr>
                <td class="label">Monto Asignado:</td>
                <td class="amount">Bs./ {{ number_format($consumo->monto_asignado, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Estado:</td>
                <td>
                    <strong style="color: 
                        {{ $consumo->estado == 'pagado' ? 'green' : 
                           ($consumo->estado == 'pendiente' ? 'orange' : 'red') }}">
                        {{ strtoupper($consumo->estado) }}
                    </strong>
                </td>
            </tr>
            @if($consumo->fecha_pago)
            <tr>
                <td class="label">Fecha de Pago:</td>
                <td>{{ $consumo->fecha_pago->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>Este documento fue generado automáticamente el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Monitoreo Inteligente - Todos los derechos reservados</p>
    </div>
</body>
</html>