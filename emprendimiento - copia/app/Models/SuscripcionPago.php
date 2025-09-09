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
        'fecha_pago'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'id_suscripcion');
    }
}