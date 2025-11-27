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
        'totalizador_m3',
        'flow_l_min',
        'bateria',
        'flags',
        'consumo_intervalo_m3',
        'tipo_registro'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'flags' => 'array',
        'totalizador_m3' => 'decimal:3',
        'flow_l_min' => 'decimal:3',
        'consumo_intervalo_m3' => 'decimal:4',
    ];

    // Relación con medidor
    public function medidor()
    {
        return $this->belongsTo(Medidor::class, 'id_medidor');
    }

    // Scope para facilitar consultas
    public function scopeDelMedidor($query, $medidorId)
    {
        return $query->where('id_medidor', $medidorId);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_hora', [$desde, $hasta]);
    }
}