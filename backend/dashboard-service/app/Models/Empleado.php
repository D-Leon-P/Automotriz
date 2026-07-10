<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->rol && $this->rol->permisos()->where('nombre', $permissionName)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->rol && $this->rol->nombre === 'administrador';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'nombre' => $this->nombre,
            'email' => $this->email,
        ];
    }
}
