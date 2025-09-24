<?php

namespace App\Print\Providers;

use App\Models\Order;
use App\Print\Contracts\PrintProvider;
use App\Print\Contracts\SubmitOrderResult;

class NullProvider implements PrintProvider
{
    public function submit(Order $order): SubmitOrderResult
    {
        // Simulate provider submission
        $external = 'NULL-' . $order->id . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        $order->update([
            'status' => 'submitted',
            'external_id' => $external,
            'data' => array_merge($order->data ?? [], [
                'provider' => 'null',
                'submitted_at' => now()->toIso8601String(),
            ]),
        ]);

        return new class($external) implements SubmitOrderResult {
            public function __construct(private ?string $id) {}
            public function externalId(): ?string { return $this->id; }
            public function raw(): array { return ['mock' => true]; }
        };
    }

    public function supports(Order $order): bool
    {
        return true;
    }
}

