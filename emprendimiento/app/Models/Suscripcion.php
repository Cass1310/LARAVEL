<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripcion';

    protected $fillable = [
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_cliente'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function pagos()
    {
        return $this->hasMany(SuscripcionPago::class, 'id_suscripcion');
    }

    public function scopeActiva($query)
    {
        return $query->where('estado', 'activa')
                    ->where('fecha_fin', '>=', now());
    }

    public function getPrecioMensualAttribute()
    {
        return $this->tipo === 'anual' ? 99.90 : 129.90;
    }

    public function getMontoTotalAttribute()
    {
        return $this->tipo === 'anual' ? $this->precio_mensual * 12 : $this->precio_mensual;
    }

    public function proximoPago()
    {
        return $this->pagos()
            ->where('estado', 'pendiente')
            ->orderBy('periodo')
            ->first();
    }

    public function pagosPagados()
    {
        return $this->pagos()->pagados()->get();
    }
}