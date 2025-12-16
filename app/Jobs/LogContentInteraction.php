<?php

namespace App\Jobs;

use App\Models\ContentLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogContentInteraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $contentId,
        public string $action,
        public ?string $sessionId,
        public ?string $ipAddress,
        public ?string $userAgent
    ) {
        $this->onConnection('rabbitmq');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ContentLog::create([
            'content_id' => $this->contentId,
            'action' => $this->action,
            'session_id' => $this->sessionId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ]);

        try {
            Http::timeout(5)
                ->post(config('services.analytics.webhook_url'), [
                    'event' => 'content_interaction',
                    'content_id' => $this->contentId,
                    'action' => $this->action,
                    'session_id' => $this->sessionId,
                    'timestamp' => now()->toIso8601String(),
                    'metadata' => [
                        'ip_address' => $this->ipAddress,
                        'user_agent' => $this->userAgent,
                    ],
                ]);

            Log::info('Analytics webhook sent successfully', [
                'content_id' => $this->contentId,
                'action' => $this->action,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('Analytics webhook failed', [
                'content_id' => $this->contentId,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

