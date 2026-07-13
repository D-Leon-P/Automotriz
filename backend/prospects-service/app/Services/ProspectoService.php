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

    public function getAllProspectos($empleadoId)
    {
        return $this->prospectoRepository->allForVendedor($empleadoId);
    }

    public function getProspectoById($id, $empleadoId)
    {
        return $this->prospectoRepository->findForVendedor($id, $empleadoId);
    }

    public function createProspecto(array $data)
    {
        $prospecto = $this->prospectoRepository->create($data);
        
        // Disparar evento a n8n
        $this->notifyN8n('created', $prospecto);

        return $prospecto;
    }

    public function updateProspecto($id, array $data, $empleadoId = null)
    {
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
