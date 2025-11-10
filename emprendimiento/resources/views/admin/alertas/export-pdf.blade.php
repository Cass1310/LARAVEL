<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Alertas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .title { font-size: 14px; font-weight: bold; }
        .filtros { background-color: #f8f9fa; padding: 8px; border-radius: 4px; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 5px; text-align: center; }
        .table th { background-color: #f8f9fa; font-weight: bold; }
        .footer { margin-top: 15px; text-align: center; font-size: 8px; color: #666; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-info { background-color: #0dcaf0; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">REPORTE DE ALERTAS</div>
        <div>Generado el: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if(!empty(array_filter($filtros)))
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        @foreach($filtros as $key => $value)
            @if($value)
                {{ ucfirst($key) }}: {{ $value }}{{ !$loop->last ? ' | ' : '' }}
            @endif
        @endforeach
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Medidor</th>
                <th>Departamento</th>
                <th>Edificio</th>
                <th>Tipo</th>
                <th>Valor (m³)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alertas as $alerta)
            <tr>
                <td>{{ $alerta->fecha_hora->format('d/m/Y H:i') }}</td>
                <td>{{ $alerta->medidor->codigo_lorawan }}</td>
                <td>{{ $alerta->medidor->departamento->numero_departamento }}</td>
                <td>{{ $alerta->medidor->departamento->edificio->nombre }}</td>
                <td>
                    @switch($alerta->tipo_alerta)
                        @case('fuga')<span class="badge badge-danger">Fuga</span>@break
                        @case('consumo_brusco')<span class="badge badge-warning">Consumo Brusco</span>@break
                        @case('consumo_excesivo')<span class="badge badge-info">Consumo Excesivo</span>@break
                    @endswitch
                </td>
                <td>{{ number_format($alerta->valor_detectado, 2) }}</td>
                <td>{{ ucfirst($alerta->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total de alertas: {{ $alertas->count() }}</p>
        <p>Sistema de Monitoreo Inteligente</p>
    </div>
</body>
</html>