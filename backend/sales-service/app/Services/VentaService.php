<?php

namespace App\Services;

use App\Repositories\VentaRepository;
use App\Models\Prospecto;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VentaService
{
    protected $ventaRepository;

    public function __construct(VentaRepository $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function getAllVentas($empleadoId, $showDeleted = false)
    {
        return $this->ventaRepository->allForVendedor($empleadoId, $showDeleted);
    }

    public function getVentaById($id, $empleadoId)
    {
        return $this->ventaRepository->findForVendedor($id, $empleadoId);
    }

    public function createVenta(array $data, $empleadoId = null)
    {
        $venta = DB::transaction(function () use ($data, $empleadoId) {
            // 1. Obtener y validar el prospecto
            $prospecto = Prospecto::findOrFail($data['prospecto_id']);
            if ($empleadoId !== null && $prospecto->empleado_id !== $empleadoId) {
                throw new \Exception("No autorizado para operar con este prospecto.");
            }
            
            // 2. Obtener el vehículo
            $vehiculo = Vehiculo::findOrFail($data['vehiculo_id']);

            // 3. Si la venta es efectiva, validar y decrementar stock
            if ($data['estado'] === 'efectiva') {
                if ($vehiculo->stock <= 0) {
                    throw new \Exception("No hay stock suficiente del vehículo seleccionado.");
                }
                $vehiculo->decrement('stock');
            }

            // 4. Crear la venta (forzando empleado_id si no es admin, o usando el del prospecto / del request si lo es)
            if ($empleadoId !== null) {
                $data['empleado_id'] = $empleadoId;
            } else {
                $data['empleado_id'] = $data['empleado_id'] ?? $prospecto->empleado_id;
            }
            
            $venta = $this->ventaRepository->create($data);

            // 5. Actualizar etapa del prospecto a 'cierre'
            $prospecto->update(['etapa' => 'cierre']);

            return $venta;
        });

        // 6. Notificar a n8n fuera de la transacción para evitar lock contention en la BD
        $this->notifyN8n('created', $venta);

        return $venta;
    }

    public function updateVenta($id, array $data, $empleadoId = null)
    {
        $updatedVenta = DB::transaction(function () use ($id, $data, $empleadoId) {
            $venta = $this->ventaRepository->findForVendedor($id, $empleadoId);
            if (!$venta) {
                throw new \Exception("Venta no encontrada o no autorizada.");
            }

            $originalEstado = $venta->estado;
            $nuevoEstado = $data['estado'] ?? $originalEstado;

            // Manejo de cambios de estado que impactan stock
            if ($originalEstado !== $nuevoEstado) {
                $vehiculo = Vehiculo::findOrFail($venta->vehiculo_id);
                if ($originalEstado === 'fallida' && $nuevoEstado === 'efectiva') {
                    if ($vehiculo->stock <= 0) {
                        throw new \Exception("No hay stock suficiente para cambiar la venta a efectiva.");
                    }
                    $vehiculo->decrement('stock');
                } elseif ($originalEstado === 'efectiva' && $nuevoEstado === 'fallida') {
                    $vehiculo->increment('stock');
                }
            }

            // Evitar transferencias maliciosas a otros empleados si no es admin
            if ($empleadoId !== null) {
                unset($data['empleado_id']);
            }

            return $this->ventaRepository->update($id, $data, $empleadoId);
        });

        $this->notifyN8n('updated', $updatedVenta);

        return $updatedVenta;
    }

    public function deleteVenta($id, $empleadoId = null)
    {
        $result = DB::transaction(function () use ($id, $empleadoId, &$venta) {
            $venta = $this->ventaRepository->findForVendedor($id, $empleadoId);
            if (!$venta) {
                return false;
            }

            if ($venta->estado === 'efectiva') {
                $vehiculo = Vehiculo::find($venta->vehiculo_id);
                if ($vehiculo) {
                    $vehiculo->increment('stock');
                }
            }

            return $this->ventaRepository->delete($id, $empleadoId);
        });

        if ($result && isset($venta)) {
            $this->notifyN8n('deleted', $venta);
        }

        return $result;
    }

    public function restoreVenta($id, $empleadoId = null)
    {
        $venta = DB::transaction(function () use ($id, $empleadoId) {
            $venta = $this->ventaRepository->findTrashedForVendedor($id, $empleadoId);
            if (!$venta) {
                throw new \Exception("Venta no encontrada o no autorizada.");
            }

            if ($venta->estado === 'efectiva') {
                $vehiculo = Vehiculo::findOrFail($venta->vehiculo_id);
                if ($vehiculo->stock <= 0) {
                    throw new \Exception("No hay stock disponible del vehículo para reintegrar esta venta.");
                }
                $vehiculo->decrement('stock');
            }

            $venta->restore();
            return $venta;
        });

        $this->notifyN8n('updated', $venta);

        return $venta;
    }

    protected function notifyN8n($action, $venta)
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/ventas');
            Http::timeout(0.5)->post($n8nUrl, [
                'action' => $action,
                'venta' => $venta->load(['prospecto', 'vehiculo', 'empleado'])->toArray(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error al notificar venta a n8n: " . $e->getMessage());
        }
    }
}
