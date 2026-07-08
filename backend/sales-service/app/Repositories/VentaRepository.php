<?php

namespace App\Repositories;

use App\Models\Venta;

class VentaRepository
{
    public function allForVendedor($vendedorId)
    {
        return Venta::with(['prospecto', 'vehiculo', 'vendedor'])
            ->where('vendedor_id', $vendedorId)
            ->get();
    }

    public function findForVendedor($id, $vendedorId)
    {
        return Venta::with(['prospecto', 'vehiculo', 'vendedor'])
            ->where('vendedor_id', $vendedorId)
            ->where('id', $id)
            ->first();
    }

    public function create(array $data)
    {
        return Venta::create($data);
    }

    public function update($id, array $data, $vendedorId)
    {
        $venta = Venta::where('id', $id)
            ->where('vendedor_id', $vendedorId)
            ->firstOrFail();
        $venta->update($data);
        return $venta;
    }

    public function delete($id, $vendedorId)
    {
        $venta = Venta::where('id', $id)
            ->where('vendedor_id', $vendedorId)
            ->firstOrFail();
        return $venta->delete();
    }
}
