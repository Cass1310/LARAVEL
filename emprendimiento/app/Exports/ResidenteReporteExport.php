<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResidenteReporteExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $user;
    protected $year;

    public function __construct(User $user, $year)
    {
        $this->user = $user;
        $this->year = $year;
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
            return collect();
        }

        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $data = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $consumo = $departamento->medidores->sum(function($medidor) use ($mes) {
                return $medidor->consumos()
                    ->whereYear('fecha_hora', $this->year)
                    ->whereMonth('fecha_hora', $mes)
                    ->sum('volumen');
            });

            $alertas = $departamento->medidores->sum(function($medidor) use ($mes) {
                return $medidor->alertas()
                    ->whereYear('fecha_hora', $this->year)
                    ->whereMonth('fecha_hora', $mes)
                    ->count();
            });

            $pagos = $departamento->consumos()
                ->whereHas('consumoEdificio', function($query) use ($mes) {
                    $query->whereYear('fecha_emision', $this->year)
                          ->whereMonth('fecha_emision', $mes);
                })
                ->where('estado', 'pagado')
                ->sum('monto_asignado');

            $data[] = [
                'mes' => $meses[$mes - 1],
                'consumo_m3' => $consumo,
                'alertas' => $alertas,
                'pagos_bs' => $pagos,
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Mes',
            'Consumo (m³)',
            'Número de Alertas',
            'Pagos Realizados (Bs./)'
        ];
    }

    public function title(): string
    {
        return 'Reporte ' . $this->year;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:D' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}