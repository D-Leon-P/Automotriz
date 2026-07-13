<?php

namespace App\Http\Controllers;

use App\Models\Prospecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlertaController extends Controller
{
    /**
     * Procesar alertas de inactividad enviadas por n8n.
     */
    public function procesarInactividad(Request $request)
    {
        // 1. Validar Token del Sistema
        $authHeader = $request->header('Authorization');
        $expectedToken = 'Bearer ' . env('N8N_SYSTEM_TOKEN', 'internal_n8n_system_token');

        if ($authHeader !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado. Token de sistema inválido.'
            ], 401);
        }

        // 2. Validar payload
        $request->validate([
            'prospectos' => 'required|array'
        ]);

        $prospectos = $request->input('prospectos');

        // 3. Procesar / Registrar Alerta de Inactividad
        Log::warning("Alerta de Inactividad: Se detectaron " . count($prospectos) . " prospectos sin avance.");

        foreach ($prospectos as $prospecto) {
            Log::warning(sprintf(
                "Prospecto Inactivo ID %d: %s (Email: %s, Tel: %s) en etapa '%s'. Última actualización: %s. Asesor: %s (%s)",
                $prospecto['id'] ?? 0,
                $prospecto['nombre'] ?? 'N/D',
                $prospecto['email'] ?? 'N/D',
                $prospecto['telefono'] ?? 'N/D',
                $prospecto['etapa'] ?? 'N/D',
                $prospecto['updated_at'] ?? 'N/D',
                $prospecto['vendedor_nombre'] ?? 'N/D',
                $prospecto['vendedor_email'] ?? 'N/D'
            ));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alertas de inactividad procesadas exitosamente.',
            'alertas_procesadas' => count($prospectos)
        ]);
    }

    /**
     * Consultar prospectos inactivos (sin actualizaciones hace más de X días).
     */
    public function getInactivos(Request $request)
    {
        // 1. Validar Token del Sistema
        $authHeader = $request->header('Authorization');
        $expectedToken = 'Bearer ' . env('N8N_SYSTEM_TOKEN', 'internal_n8n_system_token');

        if ($authHeader !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado. Token de sistema inválido.'
            ], 401);
        }

        $dias = $request->query('dias', 5);

        // Query: obtener prospectos en etapas que no sean 'cierre' y inactivos hace > $dias
        $prospectos = Prospecto::with('empleado')
            ->where('etapa', '!=', 'cierre')
            ->where('updated_at', '<', now()->subDays($dias))
            ->get();

        $prospectosMapped = $prospectos->map(function ($prospecto) {
            return [
                'id' => $prospecto->id,
                'nombre' => $prospecto->nombre,
                'email' => $prospecto->email,
                'telefono' => $prospecto->telefono,
                'etapa' => $prospecto->etapa,
                'updated_at' => $prospecto->updated_at->toDateTimeString(),
                'vendedor_nombre' => $prospecto->empleado->nombre ?? 'N/D',
                'vendedor_email' => $prospecto->empleado->email ?? 'N/D'
            ];
        });

        return response()->json([
            'length' => count($prospectosMapped),
            'prospectos' => $prospectosMapped
        ]);
    }
}
