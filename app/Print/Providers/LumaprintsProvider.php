<?php

namespace App\Print\Providers;

use App\Models\Order;
use App\Print\Contracts\PrintProvider;
use App\Print\Contracts\SubmitOrderResult;
use App\Print\PrintConfig;

class LumaprintsProvider implements PrintProvider
{
    public function supports(Order $order): bool
    {
        $opts = PrintConfig::options('lumaprints');
        return !empty($opts['api_key']) && !empty(PrintConfig::endpoint('lumaprints'));
    }

    public function submit(Order $order): SubmitOrderResult
    {
        // TODO: Implement Lumaprints API submission
        $endpoint = PrintConfig::endpoint('lumaprints');
        $external = 'LUMAPRINTS-STUB-' . $order->id;
        $order->update([
            'status' => 'submitted',
            'external_id' => $external,
            'data' => array_merge($order->data ?? [], [
                'provider' => 'lumaprints',
                'endpoint' => $endpoint,
            ]),
        ]);

        return new class($external) implements SubmitOrderResult {
            public function __construct(private ?string $id) {}
            public function externalId(): ?string { return $this->id; }
            public function raw(): array { return ['stub' => true]; }
        };
    }
}

