<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = ['medidor_id', 'tipo', 'mensaje', 'fecha_hora', 'estado'];

    public function medidor()
    {
        return $this->belongsTo(Medidor::class);
    }
}
