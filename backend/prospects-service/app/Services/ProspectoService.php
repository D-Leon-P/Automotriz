<?php

namespace App\Services;

use App\Repositories\ProspectoRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProspectoService
{
    protected $prospectoRepository;

    public function __construct(ProspectoRepository $prospectoRepository)
    {
        $this->prospectoRepository = $prospectoRepository;
    }

    public function getAllProspectos($empleadoId, $showDeleted = false)
    {
        return $this->prospectoRepository->allForVendedor($empleadoId, $showDeleted);
    }

    public function getProspectoById($id, $empleadoId)
    {
        return $this->prospectoRepository->findForVendedor($id, $empleadoId);
    }

    public function createProspecto(array $data)
    {
        $vehiculoId = $data['vehiculo_id'];
        $vehiculo = \App\Models\Vehiculo::findOrFail($vehiculoId);
        $activeReservations = \App\Models\Prospecto::where('vehiculo_id', $vehiculoId)
            ->where('etapa', '!=', 'cierre')
            ->count();
        
        if ($vehiculo->stock - $activeReservations <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'vehiculo_id' => ['El vehículo seleccionado ya no tiene disponibilidad (reservado por otro asesor).']
            ]);
        }

        $prospecto = $this->prospectoRepository->create($data);
        
        // Disparar evento a n8n
        $this->notifyN8n('created', $prospecto);

        return $prospecto;
    }

    public function updateProspecto($id, array $data, $empleadoId = null)
    {
        $prospecto = \App\Models\Prospecto::findOrFail($id);
        $vehiculoId = $data['vehiculo_id'] ?? $prospecto->vehiculo_id;
        $etapa = $data['etapa'] ?? $prospecto->etapa;

        if ($etapa !== 'cierre') {
            $vehiculo = \App\Models\Vehiculo::findOrFail($vehiculoId);
            $activeReservations = \App\Models\Prospecto::where('vehiculo_id', $vehiculoId)
                ->where('etapa', '!=', 'cierre')
                ->where('id', '!=', $id)
                ->count();
            
            if ($vehiculo->stock - $activeReservations <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'vehiculo_id' => ['El vehículo seleccionado ya no tiene disponibilidad (reservado por otro asesor).']
                ]);
            }
        }

        $prospecto = $this->prospectoRepository->update($id, $data, $empleadoId);
        
        // Disparar evento a n8n
        $this->notifyN8n('updated', $prospecto);

        return $prospecto;
    }

    public function deleteProspecto($id, $empleadoId = null)
    {
        $prospecto = $this->prospectoRepository->findForVendedor($id, $empleadoId);
        if ($prospecto) {
            $this->notifyN8n('deleted', $prospecto);
            return $this->prospectoRepository->delete($id, $empleadoId);
        }
        return false;
    }

    public function restoreProspecto($id, $empleadoId = null)
    {
        $prospecto = $this->prospectoRepository->findTrashedForVendedor($id, $empleadoId);
        if (!$prospecto) {
            throw new \Exception("Prospecto no encontrado o no autorizado.");
        }

        // Validar stock antes de reintegrar
        $vehiculoId = $prospecto->vehiculo_id;
        $vehiculo = \App\Models\Vehiculo::findOrFail($vehiculoId);
        $activeReservations = \App\Models\Prospecto::where('vehiculo_id', $vehiculoId)
            ->where('etapa', '!=', 'cierre')
            ->count();
        
        if ($vehiculo->stock - $activeReservations <= 0) {
            throw new \Exception("El vehículo asignado al prospecto ya no tiene disponibilidad para reintegrarlo.");
        }

        $prospecto->restore();
        $this->notifyN8n('updated', $prospecto);
        return $prospecto;
    }

    protected function notifyN8n($action, $prospecto)
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook-test/prospectos');
            Http::timeout(2)->post($n8nUrl, [
                'action' => $action,
                'prospecto' => $prospecto->load(['vehiculo', 'empleado'])->toArray(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error al notificar a n8n: " . $e->getMessage());
        }
    }
}
