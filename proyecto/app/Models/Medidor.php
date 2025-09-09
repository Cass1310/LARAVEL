<?php

namespace App\Models;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medidor extends Model
{
    use HasFactory;
    protected $table = 'medidores';

    protected $fillable = ['device_id', 'departamento_id'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }


    public function consumos()
    {
        return $this->hasMany(ConsumoAgua::class);
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class);
    }
}
