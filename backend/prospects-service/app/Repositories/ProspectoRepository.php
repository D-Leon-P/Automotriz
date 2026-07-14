<?php

namespace App\Repositories;

use App\Models\Prospecto;

class ProspectoRepository
{
    public function allForVendedor($empleadoId, $showDeleted = false)
    {
        $query = Prospecto::with(['vehiculo', 'empleado']);
        if ($showDeleted) {
            $query->withTrashed();
        }
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->get();
    }

    public function findForVendedor($id, $empleadoId)
    {
        $query = Prospecto::with(['vehiculo', 'empleado'])->where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->first();
    }

    public function findTrashedForVendedor($id, $empleadoId)
    {
        $query = Prospecto::withTrashed()->with(['vehiculo', 'empleado'])->where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        return $query->first();
    }

    public function create(array $data)
    {
        return Prospecto::create($data);
    }

    public function update($id, array $data, $empleadoId)
    {
        $query = Prospecto::where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        $prospecto = $query->firstOrFail();
        $prospecto->update($data);
        return $prospecto;
    }

    public function delete($id, $empleadoId)
    {
        $query = Prospecto::where('id', $id);
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }
        $prospecto = $query->firstOrFail();
        return $prospecto->delete();
    }
}
