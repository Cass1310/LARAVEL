<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medidor extends Model
{
    use HasFactory;

    protected $table = 'medidor';

    protected $fillable = [
        'codigo_lorawan',
        'id_departamento',
        'id_gateway',
        'estado',
        'fecha_instalacion',
        'created_by'
    ];

    protected $casts = [
        'fecha_instalacion' => 'date',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'id_gateway');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function alertas()
    {
        return $this->hasMany(Alerta::class, 'id_medidor');
    }

    public function consumos()
    {
        return $this->hasMany(ConsumoAgua::class, 'id_medidor');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_medidor');
    }
}