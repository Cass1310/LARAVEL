<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDepartamento extends Model
{
    use HasFactory;

    protected $table = 'factura_departamento';

    protected $fillable = [
        'id_factura',
        'id_departamento',
        'monto_asignado',
        'consumo_m3',
        'porcentaje_consumo',
        'estado',
        'fecha_pago'
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
        'consumo_m3' => 'decimal:2',
        'porcentaje_consumo' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function facturaEdificio()
    {
        return $this->belongsTo(FacturaEdificio::class, 'id_factura');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function scopePending($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePaid($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeOverdue($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function markAsPaid()
    {
        $this->update([
            'estado' => 'pagado',
            'fecha_pago' => now(),
        ]);
    }
}