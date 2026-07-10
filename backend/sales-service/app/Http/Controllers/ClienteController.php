<?php

namespace App\Http\Controllers;

use App\Services\ClienteService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;

class ClienteController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->middleware('auth:api');
        $this->middleware('permission:ver_clientes')->only(['index', 'show']);
        $this->middleware('permission:gestionar_clientes')->only(['store', 'update', 'destroy']);
        $this->clienteService = $clienteService;
    }

    public function index(Request $request)
    {
        if ($request->has('documento')) {
            $cliente = $this->clienteService->getClienteByDocumento($request->documento);
            if ($cliente) {
                return response()->json($cliente);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado.'
            ], 404);
        }
        $clientes = $this->clienteService->getAllClientes();
        return response()->json($clientes);
    }

    public function show($id)
    {
        $cliente = $this->clienteService->getClienteById($id);
        if (!$cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado.'
            ], 404);
        }
        return response()->json($cliente);
    }

    public function store(StoreClienteRequest $request)
    {
        try {
            $cliente = $this->clienteService->createCliente($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Cliente registrado exitosamente.',
                'data' => $cliente
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(UpdateClienteRequest $request, $id)
    {
        try {
            $cliente = $this->clienteService->updateCliente($id, $request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Cliente actualizado exitosamente.',
                'data' => $cliente
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->clienteService->deleteCliente($id);
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cliente no encontrado.'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Cliente eliminado exitosamente (soft delete).'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
