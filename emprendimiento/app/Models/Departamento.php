<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';

    protected $fillable = [
        'id_edificio',
        'numero_departamento',
        'piso',
        'created_by'
    ];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medidores()
    {
        return $this->hasMany(Medidor::class, 'id_departamento');
    }

    public function residentes()
    {
        return $this->belongsToMany(User::class, 'residente_departamento', 'id_departamento', 'id_residente')
                    ->withPivot('fecha_inicio', 'fecha_fin')
                    ->withTimestamps();
    }
    public function facturas()
    {
        return $this->hasMany(FacturaDepartamento::class, 'id_departamento');
    }
}