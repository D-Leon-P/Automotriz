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
    protected $ventaData;

    /**
     * Create a new job instance.
     */
    public function __construct(string $action, array $ventaData)
    {
        $this->action = $action;
        $this->ventaData = $ventaData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $n8nUrl = env('N8N_WEBHOOK_URL', 'http://n8n:5678/webhook/ventas');
            Http::timeout(5)->post($n8nUrl, [
                'action' => $this->action,
                'venta' => $this->ventaData,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error("Error en Job de notificación a n8n (sales): " . $e->getMessage());
        }
    }
}
