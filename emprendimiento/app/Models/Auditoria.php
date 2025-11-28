<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $fillable = [
        'user_id',
        'user_nombre',
        'user_email',
        'user_rol',
        'accion',
        'modulo',
        'descripcion',
        'ip_address',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos'
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeLoginLogout($query)
    {
        return $query->whereIn('accion', ['login', 'logout']);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorRol($query, $rol)
    {
        return $query->where('user_rol', $rol);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        $query->whereDate('created_at', '>=', $fechaInicio);
        
        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }
        
        return $query;
    }
}