<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Todas las Notas de Consumo</title>
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
        .summary { 
            margin-bottom: 20px; 
        }
        .summary-box { 
            border: 1px solid #ddd; 
            padding: 10px; 
            margin-bottom: 10px; 
            text-align: center;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            font-size: 10px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 6px; 
            text-align: left; 
        }
        .table th { 
            background-color: #f8f9fa; 
            font-weight: bold; 
        }
        .table tfoot { 
            background-color: #333; 
            color: white; 
        }
        .text-end { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .badge { 
            padding: 3px 6px; 
            border-radius: 3px; 
            font-size: 9px; 
        }
        .badge-success { 
            background-color: #28a745; 
            color: white; 
        }
        .badge-warning { 
            background-color: #ffc107; 
            color: black; 
        }
        .filters {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Todas las Notas de Consumo</h1>
        <h3>Propietario: {{ $user->nombre }}</h3>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
        
        @if($edificio)
            <p><strong>Edificio:</strong> {{ $edificio->nombre }}</p>
        @endif
        
        @if($fechaInicio || $fechaFin)
            <p>
                <strong>Período:</strong> 
                {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') : 'Inicio' }} 
                - 
                {{ $fechaFin ? \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') : 'Fin' }}
            </p>
        @endif
        
        @if($estado)
            <p><strong>Estado:</strong> {{ ucfirst($estado) }}</p>
        @endif
    </div>

    <!-- Resumen Estadístico -->
    <div class="summary">
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Notas</strong><br>
                    {{ $estadisticas['total_notas'] }}
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Monto</strong><br>
                    Bs./ {{ number_format($estadisticas['total_monto'], 2) }}
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Total Consumo</strong><br>
                    {{ number_format($estadisticas['total_consumo'], 2) }} m³
                </td>
                <td style="width: 25%;" class="summary-box">
                    <strong>Promedio por Nota</strong><br>
                    Bs./ {{ number_format($estadisticas['promedio_monto'], 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Distribución por Estado -->
    @if(count($estadisticas['estados']) > 0)
    <div style="margin-bottom: 20px;">
        <h4 style="text-align: center; margin-bottom: 10px;">Distribución por Estado</h4>
        <table style="width: 100%; margin-bottom: 15px;">
            <tr>
                @foreach($estadisticas['estados'] as $estadoItem => $cantidad)
                <td style="width: {{ 100 / count($estadisticas['estados']) }}%; text-align: center; border: 1px solid #ddd; padding: 8px;">
                    <strong>{{ ucfirst($estadoItem) }}</strong><br>
                    {{ $cantidad }} notas<br>
                    <small>Bs./ {{ number_format($estadisticas['monto_por_estado'][$estadoItem] ?? 0, 2) }}</small>
                </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif

    <!-- Tabla de Notas de Consumo -->
    @if(count($notasConsumo) > 0)
        <h4 style="text-align: center; margin-bottom: 10px;">Detalle de Notas de Consumo</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Edificio</th>
                    <th>Departamento</th>
                    <th>Residentes</th>
                    <th class="text-end">Consumo (m³)</th>
                    <th class="text-end">Porcentaje</th>
                    <th class="text-end">Monto (Bs.)</th>
                    <th>Estado</th>
                    <th>Período</th>
                    <th>Vencimiento</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentEdificio = null;
                    $edificioSubtotalMonto = 0;
                    $edificioSubtotalConsumo = 0;
                    $edificioCount = 0;
                @endphp
                
                @foreach($notasConsumo as $index => $nota)
                    @if($currentEdificio !== $nota['edificio'])
                        @if($currentEdificio !== null)
                            <!-- Subtotal del edificio anterior -->
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="3" style="text-align: right; font-weight: bold;">
                                    Subtotal {{ $currentEdificio }} ({{ $edificioCount }} notas):
                                </td>
                                <td class="text-end" style="font-weight: bold;">{{ number_format($edificioSubtotalConsumo, 2) }}</td>
                                <td class="text-end" style="font-weight: bold;">100%</td>
                                <td class="text-end" style="font-weight: bold;">Bs./ {{ number_format($edificioSubtotalMonto, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        @endif
                        
                        @php
                            $currentEdificio = $nota['edificio'];
                            $edificioSubtotalMonto = 0;
                            $edificioSubtotalConsumo = 0;
                            $edificioCount = 0;
                        @endphp
                        
                        <!-- Encabezado del nuevo edificio -->
                        <tr style="background-color: #e9ecef; font-weight: bold;">
                            <td colspan="9" style="padding: 8px;">
                                EDIFICIO: {{ $currentEdificio }}
                            </td>
                        </tr>
                    @endif
                    
                    <tr>
                        <td>{{ $nota['edificio'] }}</td>
                        <td>Depto {{ $nota['departamento'] }}</td>
                        <td>
                            {{ $nota['residentes'] }}
                            @if($nota['cantidad_residentes'] > 1)
                                ({{ $nota['cantidad_residentes'] }})
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($nota['consumo_m3'], 2) }}</td>
                        <td class="text-end">{{ number_format($nota['porcentaje_consumo'], 1) }}%</td>
                        <td class="text-end">Bs./ {{ number_format($nota['monto_asignado'], 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $nota['estado'] == 'pagado' ? 'success' : 'warning' }}">
                                {{ ucfirst($nota['estado']) }}
                            </span>
                        </td>
                        <td>{{ $nota['periodo'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($nota['fecha_vencimiento'])->format('d/m/Y') }}</td>
                    </tr>
                    
                    @php
                        $edificioSubtotalMonto += $nota['monto_asignado'];
                        $edificioSubtotalConsumo += $nota['consumo_m3'];
                        $edificioCount++;
                    @endphp
                    
                    <!-- Salto de página cada 25 registros -->
                    @if(($index + 1) % 25 == 0 && ($index + 1) < count($notasConsumo))
                        </tbody>
                        </table>
                        
                        <div class="page-break"></div>
                        
                        <div class="header">
                            <h3>Reporte de Todas las Notas de Consumo (Continuación)</h3>
                            <p>Página {{ ceil(($index + 1) / 25) + 1 }} - Generado el: {{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Edificio</th>
                                    <th>Departamento</th>
                                    <th>Residentes</th>
                                    <th class="text-end">Consumo (m³)</th>
                                    <th class="text-end">Porcentaje</th>
                                    <th class="text-end">Monto (Bs.)</th>
                                    <th>Estado</th>
                                    <th>Período</th>
                                    <th>Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                    @endif
                @endforeach
                
                <!-- Subtotal del último edificio -->
                @if($currentEdificio !== null)
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="3" style="text-align: right; font-weight: bold;">
                            Subtotal {{ $currentEdificio }} ({{ $edificioCount }} notas):
                        </td>
                        <td class="text-end" style="font-weight: bold;">{{ number_format($edificioSubtotalConsumo, 2) }}</td>
                        <td class="text-end" style="font-weight: bold;">100%</td>
                        <td class="text-end" style="font-weight: bold;">Bs./ {{ number_format($edificioSubtotalMonto, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL GENERAL ({{ $estadisticas['total_notas'] }} notas):</td>
                    <td class="text-end" style="font-weight: bold;">{{ number_format($estadisticas['total_consumo'], 2) }} m³</td>
                    <td class="text-end" style="font-weight: bold;">100%</td>
                    <td class="text-end" style="font-weight: bold;">Bs./ {{ number_format($estadisticas['total_monto'], 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Resumen final -->
        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h4 style="text-align: center; margin-bottom: 10px;">Resumen Ejecutivo</h4>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 5px;">
                        <strong>Total de Notas:</strong> {{ $estadisticas['total_notas'] }}
                    </td>
                    <td style="width: 50%; padding: 5px;">
                        <strong>Total Monto Generado:</strong> Bs./ {{ number_format($estadisticas['total_monto'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;">
                        <strong>Total Consumo:</strong> {{ number_format($estadisticas['total_consumo'], 2) }} m³
                    </td>
                    <td style="padding: 5px;">
                        <strong>Promedio por Nota:</strong> Bs./ {{ number_format($estadisticas['promedio_monto'], 2) }}
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div style="text-align: center; color: #666; margin: 40px 0; padding: 20px; border: 1px solid #ddd;">
            <h4>No hay notas de consumo que coincidan con los filtros seleccionados</h4>
            <p>No se encontraron registros con los criterios de búsqueda aplicados.</p>
        </div>
    @endif
</body>
</html>