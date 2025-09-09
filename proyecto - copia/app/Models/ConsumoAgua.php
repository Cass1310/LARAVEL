<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoAgua extends Model
{
    use HasFactory;

    protected $table = 'consumos_agua';

    protected $fillable = ['medidor_id', 'fecha_hora', 'litros', 'caudal', 'voltaje_bateria'];

    public function medidor()
    {
        return $this->belongsTo(Medidor::class);
    }
}
