<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyN8nJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $action;
    protected $prospectoData;

    /**
     * Create a new job instance.
     */
    public function __construct(string $action, array $prospectoData)
    {
        $this->action = $action;
        $this->prospectoData = $prospectoData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/prospectos');
            Http::timeout(5)->post($n8nUrl, [
                'action' => $this->action,
                'prospecto' => $this->prospectoData,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error en Job de notificación a n8n (prospects): " . $e->getMessage());
        }
    }
}
