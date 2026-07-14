<?php

namespace App\Repositories;

use App\Models\Cliente;

class ClienteRepository
{
    public function all()
    {
        return Cliente::get();
    }

    public function findByDocumento($documento)
    {
        return Cliente::where('documento', $documento)->first();
    }

    public function find($id)
    {
        return Cliente::find($id);
    }

    public function create(array $data)
    {
        // Primero buscar por documento (identificador principal de negocio)
        $existingByDoc = Cliente::withTrashed()->where('documento', $data['documento'])->first();
        if ($existingByDoc) {
            if ($existingByDoc->trashed()) {
                $existingByDoc->restore();
                $existingByDoc->update($data);
                return $existingByDoc;
            }
        }

        // Si no se encuentra por documento, pero el correo ya estaba registrado en un soft-deleted
        if (!empty($data['email'])) {
            $existingByEmail = Cliente::withTrashed()->where('email', $data['email'])->first();
            if ($existingByEmail) {
                if ($existingByEmail->trashed()) {
                    $existingByEmail->restore();
                    $existingByEmail->update($data);
                    return $existingByEmail;
                }
            }
        }

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
