<?php

namespace App\Repositories;

use App\Models\Seguro;

class SeguroRepository
{
    public function allForVendedor($vendedorId)
    {
        return Seguro::with('venta')
            ->whereHas('venta', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
            ->get();
    }

    public function findForVendedor($id, $vendedorId)
    {
        return Seguro::with('venta')
            ->where('id', $id)
            ->whereHas('venta', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
            ->first();
    }

    public function create(array $data)
    {
        return Seguro::create($data);
    }

    public function update($id, array $data, $vendedorId)
    {
        $seguro = Seguro::where('id', $id)
            ->whereHas('venta', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
            ->firstOrFail();
            
        $seguro->update($data);
        return $seguro;
    }

    public function delete($id, $vendedorId)
    {
        $seguro = Seguro::where('id', $id)
            ->whereHas('venta', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
            ->firstOrFail();
            
        return $seguro->delete();
    }
}
