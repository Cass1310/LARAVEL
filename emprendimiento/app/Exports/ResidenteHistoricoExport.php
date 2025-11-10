<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResidenteHistoricoExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $departamento = $this->user->departamentosResidente()
            ->where(function($query) {
                $query->where('fecha_fin', '>=', now())
                      ->orWhereNull('fecha_fin');
            })
            ->first();

        if (!$departamento) {
            return collect([['message' => 'No tiene departamento asignado']]);
        }

        $consumos = $departamento->consumos()
            ->with('consumoEdificio')
            ->whereHas('consumoEdificio', function($query) {
                $query->where('fecha_emision', '>=', now()->subMonths(5));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($consumos->isEmpty()) {
            return collect([['message' => 'No tiene ninguna nota de consumo en los últimos 5 meses']]);
        }

        $data = $consumos->map(function($consumo) {
            return [
                'periodo' => $consumo->consumoEdificio->periodo,
                'consumo_m3' => $consumo->consumo_m3,
                'porcentaje' => $consumo->porcentaje_consumo . '%',
                'monto' => 'Bs./ ' . number_format($consumo->monto_asignado, 2),
                'estado' => $consumo->estado,
                'fecha_pago' => $consumo->fecha_pago ? $consumo->fecha_pago->format('d/m/Y') : 'No pagado',
            ];
        });

        return $data;
    }

    public function headings(): array
    {
        return [
            'Periodo',
            'Consumo (m³)',
            'Porcentaje',
            'Monto',
            'Estado',
            'Fecha de Pago'
        ];
    }

    public function title(): string
    {
        return 'Histórico 5 Meses';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:F' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}