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

    public function getAllProspectos($vendedorId)
    {
        return $this->prospectoRepository->allForVendedor($vendedorId);
    }

    public function getProspectoById($id, $vendedorId)
    {
        return $this->prospectoRepository->findForVendedor($id, $vendedorId);
    }

    public function createProspecto(array $data)
    {
        $prospecto = $this->prospectoRepository->create($data);
        
        // Disparar evento a n8n
        $this->notifyN8n('created', $prospecto);

        return $prospecto;
    }

    public function updateProspecto($id, array $data, $vendedorId)
    {
        $prospecto = $this->prospectoRepository->update($id, $data, $vendedorId);
        
        // Disparar evento a n8n
        $this->notifyN8n('updated', $prospecto);

        return $prospecto;
    }

    public function deleteProspecto($id, $vendedorId)
    {
        $prospecto = $this->prospectoRepository->findForVendedor($id, $vendedorId);
        if ($prospecto) {
            $this->notifyN8n('deleted', $prospecto);
            return $this->prospectoRepository->delete($id, $vendedorId);
        }
        return false;
    }

    protected function notifyN8n($action, $prospecto)
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/prospectos');
            Http::timeout(2)->post($n8nUrl, [
                'action' => $action,
                'prospecto' => $prospecto->load(['vehiculo', 'vendedor'])->toArray(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error al notificar a n8n: " . $e->getMessage());
        }
    }
}
