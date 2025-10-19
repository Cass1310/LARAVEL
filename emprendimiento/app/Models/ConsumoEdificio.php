<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoEdificio extends Model
{
    use HasFactory;

    protected $table = 'consumo_edificio';

    protected $fillable = [
        'id_edificio',
        'periodo',
        'monto_total',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'created_by'
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consumosDepartamento()
    {
        return $this->hasMany(consumoDepartamento::class, 'id_consumo');
    }

    public function scopePending($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePaid($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function scopeOverdue($query)
    {
        return $query->where('estado', 'vencida');
    }
}