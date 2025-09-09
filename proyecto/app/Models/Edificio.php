<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edificio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'direccion'];
    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }
}
