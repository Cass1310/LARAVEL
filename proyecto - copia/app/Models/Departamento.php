<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = ['numero', 'edificio_id'];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class);
    }

    public function medidores()
    {
        return $this->hasMany(Medidor::class);
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
