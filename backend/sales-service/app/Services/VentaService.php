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
        return DB::transaction(function () use ($data, $empleadoId) {
            // 1. Obtener y validar el prospecto (Evitar BOLA: Asegurar que pertenece al empleado si no es admin)
            $prospecto = Prospecto::findOrFail($data['prospecto_id']);
            if ($empleadoId !== null && $prospecto->empleado_id !== $empleadoId) {
                throw new \Exception("No autorizado para operar con este prospecto.");
            }

            // Validar que el prospecto no tenga ya una venta registrada
            if (DB::table('ventas')->where('prospecto_id', $data['prospecto_id'])->whereNull('deleted_at')->exists()) {
                throw new \Exception("El prospecto ya tiene una venta asociada.");
            }

            // Sincronizar el vehículo del prospecto con el de la venta si difieren
            if ($prospecto->vehiculo_id !== $data['vehiculo_id']) {
                $prospecto->update(['vehiculo_id' => $data['vehiculo_id']]);
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

            // 6. Notificar a n8n
            $this->notifyN8n('created', $venta);

            return $venta;
        });
    }

    public function updateVenta($id, array $data, $empleadoId = null)
    {
        return DB::transaction(function () use ($id, $data, $empleadoId) {
            $venta = $this->ventaRepository->findForVendedor($id, $empleadoId);
            if (!$venta) {
                throw new \Exception("Venta no encontrada o no autorizada.");
            }

            $originalEstado = $venta->estado;
            $nuevoEstado = $data['estado'] ?? $originalEstado;

            $originalVehiculoId = $venta->vehiculo_id;
            $nuevoVehiculoId = $data['vehiculo_id'] ?? $originalVehiculoId;

            $originalProspectoId = $venta->prospecto_id;
            $nuevoProspectoId = $data['prospecto_id'] ?? $originalProspectoId;

            // 1. Validar prospecto si cambia
            if ($originalProspectoId !== $nuevoProspectoId) {
                if (DB::table('ventas')
                    ->where('prospecto_id', $nuevoProspectoId)
                    ->where('id', '!=', $id)
                    ->whereNull('deleted_at')
                    ->exists()) {
                    throw new \Exception("El nuevo prospecto seleccionado ya tiene una venta asociada.");
                }
            }

            // 2. Manejo de cambios de vehículo o estado que impactan stock
            if ($originalVehiculoId !== $nuevoVehiculoId || $originalEstado !== $nuevoEstado) {
                // Deshacer el stock de la venta original si era efectiva
                if ($originalEstado === 'efectiva') {
                    $originalVehiculo = Vehiculo::find($originalVehiculoId);
                    if ($originalVehiculo) {
                        $originalVehiculo->increment('stock');
                    }
                }

                // Aplicar el stock de la nueva venta si es efectiva
                if ($nuevoEstado === 'efectiva') {
                    $nuevoVehiculo = Vehiculo::findOrFail($nuevoVehiculoId);
                    if ($nuevoVehiculo->stock <= 0) {
                        throw new \Exception("No hay stock suficiente del vehículo seleccionado.");
                    }
                    $nuevoVehiculo->decrement('stock');
                }
            }

            // Evitar transferencias maliciosas a otros empleados si no es admin
            if ($empleadoId !== null) {
                unset($data['empleado_id']);
            }

            // 3. Sincronizar el vehículo del prospecto con el de la venta si difieren
            $prospecto = Prospecto::findOrFail($nuevoProspectoId);
            if ($prospecto->vehiculo_id !== $nuevoVehiculoId) {
                $prospecto->update(['vehiculo_id' => $nuevoVehiculoId]);
            }

            $updatedVenta = $this->ventaRepository->update($id, $data, $empleadoId);

            $this->notifyN8n('updated', $updatedVenta);

            return $updatedVenta;
        });
    }

    public function deleteVenta($id, $empleadoId = null)
    {
        return DB::transaction(function () use ($id, $empleadoId) {
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

            $this->notifyN8n('deleted', $venta);
            return $this->ventaRepository->delete($id, $empleadoId);
        });
    }

    public function restoreVenta($id, $empleadoId = null)
    {
        return DB::transaction(function () use ($id, $empleadoId) {
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
            $this->notifyN8n('updated', $venta);
            return $venta;
        });
    }

    protected function notifyN8n($action, $venta)
    {
        try {
            $ventaData = $venta->load(['prospecto', 'vehiculo', 'empleado'])->toArray();
            \App\Jobs\NotifyN8nJob::dispatch($action, $ventaData);
        } catch (\Exception $e) {
            Log::error("Error al despachar job de venta a n8n: " . $e->getMessage());
        }
    }
}
