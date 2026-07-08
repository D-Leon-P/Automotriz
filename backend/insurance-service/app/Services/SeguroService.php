<?php

namespace App\Services;

use App\Repositories\SeguroRepository;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeguroService
{
    protected $seguroRepository;

    public function __construct(SeguroRepository $seguroRepository)
    {
        $this->seguroRepository = $seguroRepository;
    }

    public function getAllSeguros($vendedorId)
    {
        return $this->seguroRepository->allForVendedor($vendedorId);
    }

    public function getSeguroById($id, $vendedorId)
    {
        return $this->seguroRepository->findForVendedor($id, $vendedorId);
    }

    public function createSeguro(array $data, $vendedorId)
    {
        // Validar BOLA: Asegurar que la venta pertenece al vendedor autenticado
        $venta = Venta::findOrFail($data['venta_id']);
        if ($venta->vendedor_id !== $vendedorId) {
            throw new \Exception("No autorizado para operar con esta venta.");
        }

        $seguro = $this->seguroRepository->create($data);
        $this->notifyN8n('created', $seguro);
        return $seguro;
    }

    public function updateSeguro($id, array $data, $vendedorId)
    {
        // Validar BOLA: Asegurar que el seguro original pertenece al vendedor
        $seguro = $this->seguroRepository->findForVendedor($id, $vendedorId);
        if (!$seguro) {
            throw new \Exception("Seguro no encontrado o no autorizado.");
        }

        // Si se cambia la venta asociada, validar que la nueva venta también le pertenezca
        if (isset($data['venta_id'])) {
            $venta = Venta::findOrFail($data['venta_id']);
            if ($venta->vendedor_id !== $vendedorId) {
                throw new \Exception("No autorizado para asociar la nueva venta.");
            }
        }

        $updatedSeguro = $this->seguroRepository->update($id, $data, $vendedorId);
        $this->notifyN8n('updated', $updatedSeguro);
        return $updatedSeguro;
    }

    public function deleteSeguro($id, $vendedorId)
    {
        $seguro = $this->seguroRepository->findForVendedor($id, $vendedorId);
        if ($seguro) {
            $this->notifyN8n('deleted', $seguro);
            return $this->seguroRepository->delete($id, $vendedorId);
        }
        return false;
    }

    protected function notifyN8n($action, $seguro)
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/seguros');
            Http::timeout(2)->post($n8nUrl, [
                'action' => $action,
                'seguro' => $seguro->load('venta')->toArray(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error al notificar seguro a n8n: " . $e->getMessage());
        }
    }
}
