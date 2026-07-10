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
        $tipo = $request->tipo_documento;
        $doc = $request->documento;

        if ($tipo === 'DNI') {
            if (!preg_match('/^[0-9]{8}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El DNI debe tener exactamente 8 dígitos numéricos.'
                ], 422);
            }
        } elseif ($tipo === 'RUC') {
            if (!preg_match('/^[12][0-9]{10}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El RUC debe comenzar con 1 o 2 y tener exactamente 11 dígitos numéricos.'
                ], 422);
            }
        } elseif ($tipo === 'CEX') {
            $doc = str_pad($doc, 9, '0', STR_PAD_LEFT);
            if (!preg_match('/^[a-zA-Z0-9]{9}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El CEX debe tener exactamente 9 caracteres alfanuméricos.'
                ], 422);
            }
            $request->merge(['documento' => $doc]);
        }

        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CEX',
            'nombre' => 'nullable|string|max:50',
            'apellido' => 'nullable|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'email' => 'required_without:telefono|nullable|email|max:100|unique:clientes,email',
            'telefono' => 'required_without:email|nullable|string|max:20',
            'documento' => 'required|string|max:20|unique:clientes,documento',
            'direccion' => 'nullable|string|max:255',
        ], [
            'email.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'telefono.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'fecha_nacimiento.before_or_equal' => 'El cliente debe ser mayor de edad (mínimo 18 años).'
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
        $tipo = $request->tipo_documento;
        $doc = $request->documento;

        if ($tipo === 'DNI') {
            if (!preg_match('/^[0-9]{8}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El DNI debe tener exactamente 8 dígitos numéricos.'
                ], 422);
            }
        } elseif ($tipo === 'RUC') {
            if (!preg_match('/^[12][0-9]{10}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El RUC debe comenzar con 1 o 2 y tener exactamente 11 dígitos numéricos.'
                ], 422);
            }
        } elseif ($tipo === 'CEX') {
            $doc = str_pad($doc, 9, '0', STR_PAD_LEFT);
            if (!preg_match('/^[a-zA-Z0-9]{9}$/', $doc)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El CEX debe tener exactamente 9 caracteres alfanuméricos.'
                ], 422);
            }
            $request->merge(['documento' => $doc]);
        }

        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CEX',
            'nombre' => 'nullable|string|max:50',
            'apellido' => 'nullable|string|max:50',
            'razon_social' => 'nullable|string|max:150',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'email' => 'required_without:telefono|nullable|email|max:100|unique:clientes,email,' . $id,
            'telefono' => 'required_without:email|nullable|string|max:20',
            'documento' => 'required|string|max:20|unique:clientes,documento,' . $id,
            'direccion' => 'nullable|string|max:255',
        ], [
            'email.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'telefono.required_without' => 'Debe registrar al menos un correo electrónico o un teléfono de contacto.',
            'fecha_nacimiento.before_or_equal' => 'El cliente debe ser mayor de edad (mínimo 18 años).'
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
