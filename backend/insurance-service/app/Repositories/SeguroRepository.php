<?php

namespace App\Repositories;

use App\Models\Seguro;

class SeguroRepository
{
    public function allForVendedor($empleadoId)
    {
        $query = Seguro::with('venta');
        if ($empleadoId !== null) {
            $query->whereHas('venta', function ($q) use ($empleadoId) {
                $q->where('empleado_id', $empleadoId);
            });
        }
        return $query->get();
    }

    public function findForVendedor($id, $empleadoId)
    {
        $query = Seguro::with('venta')->where('id', $id);
        if ($empleadoId !== null) {
            $query->whereHas('venta', function ($q) use ($empleadoId) {
                $q->where('empleado_id', $empleadoId);
            });
        }
        return $query->first();
    }

    public function create(array $data)
    {
        return Seguro::create($data);
    }

    public function update($id, array $data, $empleadoId)
    {
        $query = Seguro::where('id', $id);
        if ($empleadoId !== null) {
            $query->whereHas('venta', function ($q) use ($empleadoId) {
                $q->where('empleado_id', $empleadoId);
            });
        }
        $seguro = $query->firstOrFail();
        $seguro->update($data);
        return $seguro;
    }

    public function delete($id, $empleadoId)
    {
        $query = Seguro::where('id', $id);
        if ($empleadoId !== null) {
            $query->whereHas('venta', function ($q) use ($empleadoId) {
                $q->where('empleado_id', $empleadoId);
            });
        }
        $seguro = $query->firstOrFail();
        return $seguro->delete();
    }
}
