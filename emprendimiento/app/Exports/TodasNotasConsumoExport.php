<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TodasNotasConsumoExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithMapping
{
    protected $user;
    protected $notasConsumo;
    protected $edificioId;
    protected $estado;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct(User $user, array $notasConsumo, $edificioId = null, $estado = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->user = $user;
        $this->notasConsumo = $notasConsumo;
        $this->edificioId = $edificioId;
        $this->estado = $estado;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        return collect($this->notasConsumo);
    }

    public function map($nota): array
    {
        return [
            $nota['edificio'],
            'Depto ' . $nota['departamento'],
            $nota['residentes'],
            number_format($nota['consumo_m3'], 2),
            number_format($nota['porcentaje_consumo'], 2) . '%',
            'Bs./ ' . number_format($nota['monto_asignado'], 2),
            ucfirst($nota['estado']),
            $nota['periodo'],
            \Carbon\Carbon::parse($nota['fecha_emision'])->format('d/m/Y'),
            \Carbon\Carbon::parse($nota['fecha_vencimiento'])->format('d/m/Y'),
            $nota['fecha_pago'] ? \Carbon\Carbon::parse($nota['fecha_pago'])->format('d/m/Y') : 'No pagado',
        ];
    }

    public function headings(): array
    {
        return [
            'Edificio',
            'Departamento',
            'Residentes',
            'Consumo (m³)',
            'Porcentaje (%)',
            'Monto (Bs.)',
            'Estado',
            'Período',
            'Fecha Emisión',
            'Fecha Vencimiento',
            'Fecha Pago'
        ];
    }

    public function title(): string
    {
        return 'Todas las Notas de Consumo';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'F' => ['alignment' => ['horizontal' => 'right']],
            'D' => ['alignment' => ['horizontal' => 'right']],
            'E' => ['alignment' => ['horizontal' => 'right']],
        ];
    }
}