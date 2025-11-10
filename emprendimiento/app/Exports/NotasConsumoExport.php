<?php

namespace App\Exports;

use App\Models\Edificio;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotasConsumoExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $edificio;
    protected $notasConsumo;
    protected $mes;

    public function __construct(Edificio $edificio, array $notasConsumo, string $mes)
    {
        $this->edificio = $edificio;
        $this->notasConsumo = $notasConsumo;
        $this->mes = $mes;
    }

    public function collection()
    {
        return collect($this->notasConsumo)->map(function($nota) {
            return [
                'Departamento' => $nota['departamento'],
                'Residentes' => $nota['residentes'],
                'Consumo (m³)' => number_format($nota['consumo_m3'], 2),
                'Porcentaje (%)' => number_format($nota['porcentaje_consumo'], 2),
                'Monto (Bs.)' => number_format($nota['monto_asignado'], 2),
                'Estado' => ucfirst($nota['estado']),
                'Fecha Emisión' => $nota['fecha_emision'],
                'Fecha Vencimiento' => $nota['fecha_vencimiento'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Departamento',
            'Residentes',
            'Consumo (m³)',
            'Porcentaje (%)',
            'Monto (Bs.)',
            'Estado',
            'Fecha Emisión',
            'Fecha Vencimiento',
        ];
    }

    public function title(): string
    {
        return 'Notas de Consumo';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => ['font' => ['bold' => true]],
            
            // Estilo para las columnas de montos
            'E' => ['alignment' => ['horizontal' => 'right']],
            'C' => ['alignment' => ['horizontal' => 'right']],
            'D' => ['alignment' => ['horizontal' => 'right']],
        ];
    }
}