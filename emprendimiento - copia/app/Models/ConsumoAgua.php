<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoAgua extends Model
{
    use HasFactory;

    protected $table = 'consumo_agua';

    protected $fillable = [
        'id_medidor',
        'fecha_hora',
        'volumen',
        'tipo_registro'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'volumen' => 'decimal:2',
    ];

    public function medidor()
    {
        return $this->belongsTo(Medidor::class, 'id_medidor');
    }
}