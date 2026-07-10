<?php

namespace App\Http\Controllers;

use App\Services\ClienteService;
use Illuminate\Http\Request;

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

    public function index()
    {
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'edad' => 'nullable|integer|min:18',
            'email' => 'nullable|email|max:100|unique:clientes,email',
            'telefono' => 'nullable|string|max:20',
            'documento' => 'required|string|max:20|unique:clientes,documento',
            'direccion' => 'nullable|string|max:255',
        ]);

        try {
            $cliente = $this->clienteService->createCliente($validated);
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'edad' => 'nullable|integer|min:18',
            'email' => 'nullable|email|max:100|unique:clientes,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'documento' => 'required|string|max:20|unique:clientes,documento,' . $id,
            'direccion' => 'nullable|string|max:255',
        ]);

        try {
            $cliente = $this->clienteService->updateCliente($id, $validated);
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
