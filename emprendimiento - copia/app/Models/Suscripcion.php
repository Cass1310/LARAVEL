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
}