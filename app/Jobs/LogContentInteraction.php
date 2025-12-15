<?php

namespace App\Jobs;

use App\Models\ContentLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
    }
}

