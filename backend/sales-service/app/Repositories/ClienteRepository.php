<?php

namespace App\Repositories;

use App\Models\Cliente;

class ClienteRepository
{
    public function all()
    {
        return Cliente::get();
    }

    public function find($id)
    {
        return Cliente::find($id);
    }

    public function create(array $data)
    {
        return Cliente::create($data);
    }

    public function update($id, array $data)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($data);
        return $cliente;
    }

    public function delete($id)
    {
        $cliente = Cliente::findOrFail($id);
        return $cliente->delete();
    }
}
