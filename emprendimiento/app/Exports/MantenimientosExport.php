<?php

namespace App\Exports;

use App\Models\Mantenimiento;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MantenimientosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return Mantenimiento::with(['medidor.departamento.edificio'])
            ->when($this->filtros['tipo'] ?? null, function($query, $tipo) {
                return $query->where('tipo', $tipo);
            })
            ->when($this->filtros['cobertura'] ?? null, function($query, $cobertura) {
                return $query->where('cobertura', $cobertura);
            })
            ->when($this->filtros['estado'] ?? null, function($query, $estado) {
                return $query->where('estado', $estado);
            })
            ->when($this->filtros['edificio'] ?? null, function($query, $edificio) {
                return $query->whereHas('medidor.departamento.edificio', function($q) use ($edificio) {
                    $q->where('nombre', $edificio);
                });
            })
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Medidor',
            'Departamento',
            'Edificio',
            'Tipo',
            'Cobertura',
            'Costo (Bs. )',
            'Descripción',
            'Estado'
        ];
    }

    public function map($mantenimiento): array
    {
        return [
            $mantenimiento->fecha->format('d/m/Y'),
            $mantenimiento->medidor->codigo_lorawan,
            $mantenimiento->medidor->departamento->numero_departamento,
            $mantenimiento->medidor->departamento->edificio->nombre,
            ucfirst($mantenimiento->tipo),
            $this->getCobertura($mantenimiento->cobertura),
            number_format($mantenimiento->costo, 2),
            $mantenimiento->descripcion,
            $this->getEstado($mantenimiento->estado)
        ];
    }

    private function getCobertura($cobertura)
    {
        return match($cobertura) {
            'incluido_suscripcion' => 'Incluido Suscripción',
            'cobrado' => 'Cobrado',
            default => $cobertura
        };
    }

    private function getEstado($estado)
    {
        return match($estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => $estado
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:I' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}