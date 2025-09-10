<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'telefono',
        'direccion',
        'created_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_usuario');
    }

    public function usuariosCreados()
    {
        return $this->hasMany(User::class, 'created_by', 'id_usuario');
    }

    public function scopeAdministradores($query)
    {
        return $query->where('rol', 'administrador');
    }

    public function scopePropietarios($query)
    {
        return $query->where('rol', 'propietario');
    }

    public function scopeResidentes($query)
    {
        return $query->where('rol', 'residente');
    }
    public function edificiosPropietario()
    {
        return $this->hasMany(Edificio::class, 'id_propietario');
    }

    public function departamentosResidente()
    {
        return $this->belongsToMany(Departamento::class, 'residente_departamento', 'id_residente', 'id_departamento')
                    ->withPivot('fecha_inicio', 'fecha_fin')
                    ->withTimestamps();
    }

    public function elementosCreados()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id');
    }
}
