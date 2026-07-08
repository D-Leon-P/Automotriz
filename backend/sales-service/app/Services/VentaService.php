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

    public function getAllVentas($vendedorId)
    {
        return $this->ventaRepository->allForVendedor($vendedorId);
    }

    public function getVentaById($id, $vendedorId)
    {
        return $this->ventaRepository->findForVendedor($id, $vendedorId);
    }

    public function createVenta(array $data, $vendedorId)
    {
        return DB::transaction(function () use ($data, $vendedorId) {
            // 1. Obtener y validar el prospecto (Evitar BOLA: Asegurar que pertenece al vendedor autenticado)
            $prospecto = Prospecto::findOrFail($data['prospecto_id']);
            if ($prospecto->vendedor_id !== $vendedorId) {
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

            // 4. Crear la venta (forzando vendedor_id)
            $data['vendedor_id'] = $vendedorId;
            $venta = $this->ventaRepository->create($data);

            // 5. Actualizar etapa del prospecto a 'cierre'
            $prospecto->update(['etapa' => 'cierre']);

            // 6. Notificar a n8n
            $this->notifyN8n('created', $venta);

            return $venta;
        });
    }

    public function updateVenta($id, array $data, $vendedorId)
    {
        return DB::transaction(function () use ($id, $data, $vendedorId) {
            $venta = $this->ventaRepository->findForVendedor($id, $vendedorId);
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

            // Evitar transferencias maliciosas a otros asesores
            unset($data['vendedor_id']);

            $updatedVenta = $this->ventaRepository->update($id, $data, $vendedorId);

            $this->notifyN8n('updated', $updatedVenta);

            return $updatedVenta;
        });
    }

    public function deleteVenta($id, $vendedorId)
    {
        return DB::transaction(function () use ($id, $vendedorId) {
            $venta = $this->ventaRepository->findForVendedor($id, $vendedorId);
            if (!$venta) {
                return false;
            }

            if ($venta->estado === 'efectiva') {
                $vehiculo = Vehiculo::find($venta->vehiculo_id);
                if ($vehiculo) {
                    $vehiculo->increment('stock');
                }
            }

            $this->notifyN8n('deleted', $venta);
            return $this->ventaRepository->delete($id, $vendedorId);
        });
    }

    protected function notifyN8n($action, $venta)
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/ventas');
            Http::timeout(2)->post($n8nUrl, [
                'action' => $action,
                'venta' => $venta->load(['prospecto', 'vehiculo', 'vendedor'])->toArray(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error al notificar venta a n8n: " . $e->getMessage());
        }
    }
}
