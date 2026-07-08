<?php

namespace App\Repositories;

use App\Models\Vehiculo;

class VehiculoRepository
{
    public function all()
    {
        return Vehiculo::all();
    }

    public function find($id)
    {
        return Vehiculo::find($id);
    }
}
