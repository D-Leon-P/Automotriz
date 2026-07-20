<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyWebSocketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $event, array $data)
    {
        $this->event = $event;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Http::timeout(5)->post('http://websocket-service:6001/publish', [
                'event' => $this->event,
                'data' => $this->data
            ]);
        } catch (\Exception $e) {
            Log::error("Error en Job de notificación a WebSocket: " . $e->getMessage());
        }
    }
}
