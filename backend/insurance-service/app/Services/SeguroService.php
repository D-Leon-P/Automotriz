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

    public function getAllSeguros($empleadoId)
    {
        return $this->seguroRepository->allForVendedor($empleadoId);
    }

    public function getSeguroById($id, $empleadoId)
    {
        return $this->seguroRepository->findForVendedor($id, $empleadoId);
    }

    public function createSeguro(array $data, $empleadoId)
    {
        // Validar BOLA: Asegurar que la venta pertenece al empleado (si no es admin)
        $venta = Venta::findOrFail($data['venta_id']);
        if ($empleadoId !== null && $venta->empleado_id !== $empleadoId) {
            throw new \Exception("No autorizado para operar con esta venta.");
        }

        // Validar que la venta no tenga ya un seguro registrado
        if (\Illuminate\Support\Facades\DB::table('seguros')->where('venta_id', $data['venta_id'])->whereNull('deleted_at')->exists()) {
            throw new \Exception("La venta ya tiene un seguro asociado.");
        }

        $seguro = $this->seguroRepository->create($data);
        $this->notifyN8n('created', $seguro);
        return $seguro;
    }

    public function updateSeguro($id, array $data, $empleadoId)
    {
        // Validar BOLA: Asegurar que el seguro original pertenece al empleado
        $seguro = $this->seguroRepository->findForVendedor($id, $empleadoId);
        if (!$seguro) {
            throw new \Exception("Seguro no encontrado o no autorizado.");
        }

        // Si se cambia la venta asociada, validar que la nueva venta también le pertenezca
        if (isset($data['venta_id'])) {
            $venta = Venta::findOrFail($data['venta_id']);
            if ($empleadoId !== null && $venta->empleado_id !== $empleadoId) {
                throw new \Exception("No autorizado para asociar la nueva venta.");
            }
        }

        $updatedSeguro = $this->seguroRepository->update($id, $data, $empleadoId);
        $this->notifyN8n('updated', $updatedSeguro);
        return $updatedSeguro;
    }

    public function deleteSeguro($id, $empleadoId)
    {
        $seguro = $this->seguroRepository->findForVendedor($id, $empleadoId);
        if ($seguro) {
            $this->notifyN8n('deleted', $seguro);
            return $this->seguroRepository->delete($id, $empleadoId);
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
