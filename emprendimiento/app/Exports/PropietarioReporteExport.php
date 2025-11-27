<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class PropietarioReporteExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $user;
    protected $edificioId;
    protected $year;

    public function __construct(User $user, $edificioId, $year)
    {
        $this->user = $user;
        $this->edificioId = $edificioId;
        $this->year = $year;
    }

    public function collection()
    {
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $data = [];
        
        for ($mes = 1; $mes <= 12; $mes++) {
            $consumo = $this->getConsumoMes($mes);
            $alertas = $this->getAlertasMes($mes);
            $facturacion = $this->getFacturacionMes($mes);

            $data[] = [
                'mes' => $meses[$mes - 1],
                'consumo_m3' => $consumo,
                'alertas' => $alertas,
                'facturacion_bs' => $facturacion,
            ];
        }

        // Agregar totales
        $data[] = [
            'mes' => 'TOTAL',
            'consumo_m3' => array_sum(array_column($data, 'consumo_m3')),
            'alertas' => array_sum(array_column($data, 'alertas')),
            'facturacion_bs' => array_sum(array_column($data, 'facturacion_bs')),
        ];

        return collect($data);
    }

    private function getConsumoMes($mes)
    {
        $query = \App\Models\ConsumoAgua::join('medidor', 'consumo_agua.id_medidor', '=', 'medidor.id')
            ->join('departamento', 'medidor.id_departamento', '=', 'departamento.id')
            ->join('edificio', 'departamento.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', $this->user->id)
            ->whereYear('consumo_agua.fecha_hora', $this->year)
            ->whereMonth('consumo_agua.fecha_hora', $mes);

        if ($this->edificioId) {
            $query->where('edificio.id', $this->edificioId);
        }

        // CAMBIO: usar consumo_intervalo_m3 en lugar de volumen
        return $query->sum('consumo_agua.consumo_intervalo_m3');
    }

    private function getAlertasMes($mes)
    {
        $query = \App\Models\Alerta::join('medidor', 'alerta.id_medidor', '=', 'medidor.id')
            ->join('departamento', 'medidor.id_departamento', '=', 'departamento.id')
            ->join('edificio', 'departamento.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', $this->user->id)
            ->whereYear('alerta.fecha_hora', $this->year)
            ->whereMonth('alerta.fecha_hora', $mes);

        if ($this->edificioId) {
            $query->where('edificio.id', $this->edificioId);
        }

        return $query->count();
    }

    private function getFacturacionMes($mes)
    {
        $query = \App\Models\ConsumoEdificio::join('edificio', 'consumo_edificio.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', $this->user->id)
            ->whereYear('consumo_edificio.fecha_emision', $this->year)
            ->whereMonth('consumo_edificio.fecha_emision', $mes);

        if ($this->edificioId) {
            $query->where('edificio.id', $this->edificioId);
        }

        return $query->sum('consumo_edificio.monto_total');
    }

    public function headings(): array
    {
        return [
            'Mes',
            'Consumo (m³)',
            'Número de Alertas',
            'Facturación (Bs./)'
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
            13 => ['font' => ['bold' => true]],
        ];
    }
}