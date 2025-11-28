<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuscripcionPago extends Model
{
    use HasFactory;

    protected $table = 'suscripcion_pago';

    protected $fillable = [
        'id_suscripcion',
        'periodo',
        'monto',
        'estado',
        'fecha_pago',
        'comprobante',
        'metodo_pago'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'id_suscripcion');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function marcarComoPagado()
    {
        $this->update([
            'estado' => 'pagado',
            'fecha_pago' => now(),
        ]);
    }

    public function getComprobanteUrlAttribute()
    {
        if (empty($this->comprobante)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($this->comprobante);
    }
}