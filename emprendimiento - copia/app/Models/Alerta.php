<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alerta';

    protected $fillable = [
        'id_medidor',
        'tipo_alerta',
        'valor_detectado',
        'fecha_hora',
        'estado'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'valor_detectado' => 'decimal:2',
    ];

    public function medidor()
    {
        return $this->belongsTo(Medidor::class, 'id_medidor');
    }
}