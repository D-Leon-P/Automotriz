<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'prospecto_id',
        'vehiculo_id',
        'empleado_id',
        'monto',
        'estado',
        'motivo_perdida',
    ];

    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
