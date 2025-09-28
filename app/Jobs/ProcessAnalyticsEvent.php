<?php

namespace App\Jobs;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAnalyticsEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $sessionId = $this->payload['session_id'];
        $timestamp = $this->payload['timestamp'];

        $session = AnalyticsSession::firstOrNew(['session_id' => $sessionId]);
        $session->user_id = $this->payload['user_id'] ?? $session->user_id;
        $session->tenant_id = $this->payload['tenant_id'] ?? $session->tenant_id;
        $session->device = $session->device ?: ($this->payload['device'] ?? null);
        $session->first_referrer = $session->first_referrer ?: ($this->payload['referrer'] ?? null);
        $session->first_seen = $session->exists ? ($session->first_seen ?? $timestamp) : $timestamp;
        $session->last_seen = $timestamp;
        $session->save();

        AnalyticsEvent::create([
            'session_id' => $sessionId,
            'user_id' => $this->payload['user_id'] ?? null,
            'tenant_id' => $this->payload['tenant_id'] ?? null,
            'event' => $this->payload['event'],
            'meta' => $this->payload['meta'] ?? [],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
