<?php

namespace App\Exports;

use App\Models\Alerta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlertasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return Alerta::with(['medidor.departamento.edificio'])
            ->when($this->filtros['tipo'] ?? null, function($query, $tipo) {
                return $query->where('tipo_alerta', $tipo);
            })
            ->when($this->filtros['estado'] ?? null, function($query, $estado) {
                return $query->where('estado', $estado);
            })
            ->when($this->filtros['edificio'] ?? null, function($query, $edificio) {
                return $query->whereHas('medidor.departamento.edificio', function($q) use ($edificio) {
                    $q->where('nombre', $edificio);
                });
            })
            ->when($this->filtros['fecha_desde'] ?? null, function($query, $fecha) {
                return $query->where('fecha_hora', '>=', $fecha);
            })
            ->orderBy('fecha_hora', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Medidor',
            'Departamento',
            'Edificio',
            'Tipo de Alerta',
            'Valor Detectado (m³)',
            'Estado'
        ];
    }

    public function map($alerta): array
    {
        return [
            $alerta->fecha_hora->format('d/m/Y H:i'),
            $alerta->medidor->codigo_lorawan,
            $alerta->medidor->departamento->numero_departamento,
            $alerta->medidor->departamento->edificio->nombre,
            $this->getTipoAlerta($alerta->tipo_alerta),
            number_format($alerta->valor_detectado, 2),
            ucfirst($alerta->estado)
        ];
    }

    private function getTipoAlerta($tipo)
    {
        return match($tipo) {
            'fuga' => 'Fuga',
            'consumo_brusco' => 'Consumo Brusco',
            'consumo_excesivo' => 'Consumo Excesivo',
            'fuga_nocturna' => 'Fuga Nocturna',
            default => $tipo
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:G' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}