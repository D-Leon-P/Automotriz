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
}
