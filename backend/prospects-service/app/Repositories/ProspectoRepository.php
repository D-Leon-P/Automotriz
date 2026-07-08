<?php

namespace App\Repositories;

use App\Models\Prospecto;

class ProspectoRepository
{
    public function allForVendedor($vendedorId)
    {
        return Prospecto::with(['vehiculo', 'vendedor'])
            ->where('vendedor_id', $vendedorId)
            ->get();
    }

    public function findForVendedor($id, $vendedorId)
    {
        return Prospecto::with(['vehiculo', 'vendedor'])
            ->where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->first();
    }

    public function create(array $data)
    {
        return Prospecto::create($data);
    }

    public function update($id, array $data, $vendedorId)
    {
        $prospecto = Prospecto::where('id', $id)
            ->where('vendedor_id', $vendedorId)
            ->firstOrFail();
        $prospecto->update($data);
        return $prospecto;
    }

    public function delete($id, $vendedorId)
    {
        $prospecto = Prospecto::where('id', $id)
            ->where('vendedor_id', $vendedorId)
            ->firstOrFail();
        return $prospecto->delete();
    }
}
