<?php

namespace App\Exports;

use App\Models\Gateway;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GatewaysExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Gateway::withCount('medidores')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código Gateway',
            'Descripción',
            'Ubicación',
            'Medidores Asociados',
            'Fecha Creación'
        ];
    }

    public function map($gateway): array
    {
        return [
            $gateway->codigo_gateway,
            $gateway->descripcion ?? 'N/A',
            $gateway->ubicacion ?? 'N/A',
            $gateway->medidores_count,
            $gateway->created_at->format('d/m/Y')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:E' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}