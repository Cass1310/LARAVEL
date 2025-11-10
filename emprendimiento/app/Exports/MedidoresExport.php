<?php

namespace App\Exports;

use App\Models\Medidor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MedidoresExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Medidor::with(['departamento.edificio', 'gateway'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código LoRaWAN',
            'Departamento',
            'Edificio',
            'Gateway',
            'Estado',
            'Fecha Instalación',
            'Consumos Registrados',
            'Alertas Registradas'
        ];
    }

    public function map($medidor): array
    {
        return [
            $medidor->codigo_lorawan,
            $medidor->departamento->numero_departamento . ' - Piso ' . $medidor->departamento->piso,
            $medidor->departamento->edificio->nombre,
            $medidor->gateway ? $medidor->gateway->codigo_gateway : 'Sin Gateway',
            ucfirst($medidor->estado),
            $medidor->fecha_instalacion?->format('d/m/Y') ?? 'N/A',
            $medidor->consumos()->count(),
            $medidor->alertas()->count()
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:H' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}