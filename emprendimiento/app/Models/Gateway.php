<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $table = 'gateway';

    protected $fillable = [
        'codigo_gateway',
        'descripcion',
        'ubicacion'
    ];

    public function medidores()
    {
        return $this->hasMany(Medidor::class, 'id_gateway');
    }
}