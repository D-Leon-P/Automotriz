<?php

namespace App\Services;

use App\Repositories\ClienteRepository;

class ClienteService
{
    protected $clienteRepository;

    public function __construct(ClienteRepository $clienteRepository)
    {
        $this->clienteRepository = $clienteRepository;
    }

    public function getAllClientes()
    {
        return $this->clienteRepository->all();
    }

    public function getClienteById($id)
    {
        return $this->clienteRepository->find($id);
    }

    public function createCliente(array $data)
    {
        return $this->clienteRepository->create($data);
    }

    public function updateCliente($id, array $data)
    {
        return $this->clienteRepository->update($id, $data);
    }

    public function deleteCliente($id)
    {
        return $this->clienteRepository->delete($id);
    }
}
