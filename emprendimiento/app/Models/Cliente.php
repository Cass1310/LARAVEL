<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente';

    protected $fillable = [
        'nit_opcional',
        'razon_social'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'id_cliente');
    }
}