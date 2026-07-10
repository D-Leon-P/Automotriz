<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospecto extends Model
{
    use SoftDeletes;

    protected $table = 'prospectos';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'vehiculo_id',
        'etapa',
        'empleado_id',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
