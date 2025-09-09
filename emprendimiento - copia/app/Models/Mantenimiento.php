<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $table = 'mantenimiento';

    protected $fillable = [
        'id_medidor',
        'tipo',
        'cobertura',
        'costo',
        'fecha',
        'descripcion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo' => 'decimal:2',
    ];

    public function medidor()
    {
        return $this->belongsTo(Medidor::class, 'id_medidor');
    }
}