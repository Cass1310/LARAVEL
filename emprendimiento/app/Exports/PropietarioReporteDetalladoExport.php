<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PropietarioReporteDetalladoExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        $sheets = [
            new PropietarioReporteExport($this->user, $this->edificioId, $this->year),
            new PropietarioEdificiosSheet($this->user, $this->edificioId, $this->year),
            new PropietarioAlertasSheet($this->user, $this->edificioId, $this->year),
        ];

        return $sheets;
    }
}

class PropietarioEdificiosSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = \App\Models\Edificio::where('id_propietario', $this->user->id)
            ->withCount(['departamentos', 'medidores']);

        if ($this->edificioId) {
            $query->where('id', $this->edificioId);
        }

        $edificios = $query->get();

        $data = $edificios->map(function($edificio) {
            $consumo = $edificio->departamentos->sum(function($departamento) {
                return $departamento->medidores->sum(function($medidor) {
                    return $medidor->consumos()
                        ->whereYear('fecha_hora', $this->year)
                        ->sum('volumen');
                });
            });

            $facturacion = $edificio->consumos()
                ->whereYear('fecha_emision', $this->year)
                ->sum('monto_total');

            return [
                'nombre' => $edificio->nombre,
                'direccion' => $edificio->direccion,
                'departamentos' => $edificio->departamentos_count,
                'medidores' => $edificio->medidores_count,
                'consumo_m3' => $consumo,
                'facturacion_bs' => $facturacion,
            ];
        });

        return $data;
    }

    public function headings(): array
    {
        return [
            'Edificio',
            'Dirección',
            'Departamentos',
            'Medidores',
            'Consumo (m³)',
            'Facturación (Bs./)'
        ];
    }

    public function title(): string
    {
        return 'Edificios';
    }
}

class PropietarioAlertasSheet implements FromCollection, WithHeadings, WithTitle
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
        $query = \App\Models\Alerta::join('medidor', 'alerta.id_medidor', '=', 'medidor.id')
            ->join('departamento', 'medidor.id_departamento', '=', 'departamento.id')
            ->join('edificio', 'departamento.id_edificio', '=', 'edificio.id')
            ->where('edificio.id_propietario', $this->user->id)
            ->whereYear('alerta.fecha_hora', $this->year)
            ->with(['medidor.departamento.edificio']);

        if ($this->edificioId) {
            $query->where('edificio.id', $this->edificioId);
        }

        $alertas = $query->select('alerta.*')->get();

        $data = $alertas->map(function($alerta) {
            return [
                'fecha' => $alerta->fecha_hora->format('d/m/Y H:i'),
                'edificio' => $alerta->medidor->departamento->edificio->nombre,
                'departamento' => $alerta->medidor->departamento->numero_departamento,
                'tipo' => $this->getTipoAlerta($alerta->tipo_alerta),
                'valor' => $alerta->valor_detectado . ' m³',
                'estado' => ucfirst($alerta->estado),
            ];
        });

        return $data;
    }

    private function getTipoAlerta($tipo)
    {
        $tipos = [
            'fuga' => 'Fuga de Agua',
            'consumo_brusco' => 'Consumo Brusco',
            'consumo_excesivo' => 'Consumo Excesivo'
        ];

        return $tipos[$tipo] ?? $tipo;
    }

    public function headings(): array
    {
        return [
            'Fecha y Hora',
            'Edificio',
            'Departamento',
            'Tipo de Alerta',
            'Valor Detectado',
            'Estado'
        ];
    }

    public function title(): string
    {
        return 'Alertas';
    }
}