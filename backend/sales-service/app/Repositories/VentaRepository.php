<?php

namespace App\Repositories;

use App\Models\Venta;

class VentaRepository
{
    public function allForVendedor($empleadoId)
    {
        $query = Venta::with(['prospecto', 'vehiculo', 'empleado']);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->get();
    }

    public function findForVendedor($id, $empleadoId)
    {
        $query = Venta::with(['prospecto', 'vehiculo', 'empleado'])->where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->first();
    }

    public function create(array $data)
    {
        return Venta::create($data);
    }

    public function update($id, array $data, $empleadoId)
    {
        $query = Venta::where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        $venta = $query->firstOrFail();
        $venta->update($data);
        return $venta;
    }

    public function delete($id, $empleadoId)
    {
        $query = Venta::where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        $venta = $query->firstOrFail();
        return $venta->delete();
    }
}
