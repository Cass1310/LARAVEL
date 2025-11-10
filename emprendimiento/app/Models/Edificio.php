<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edificio extends Model
{
    use HasFactory;

    protected $table = 'edificio';

    protected $fillable = [
        'id_propietario',
        'nombre',
        'direccion',
        'created_by'
    ];

    public function propietario()
    {
        return $this->belongsTo(User::class, 'id_propietario');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function departamentos()
    {
        return $this->hasMany(Departamento::class, 'id_edificio');
    }
    public function medidores()
    {
        return $this->hasManyThrough(
            Medidor::class,      // Modelo final
            Departamento::class, // Modelo intermedio
            'id_edificio',       // FK de Departamento en Edificio
            'id_departamento',   // FK de Medidor en Departamento
            'id',                // PK de Edificio
            'id'                 // PK de Departamento
        );
    }

    public function consumos()
    {
        return $this->hasMany(ConsumoEdificio::class, 'id_edificio');
    }
}