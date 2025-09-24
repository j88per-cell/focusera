<?php

namespace App\Print\Providers;

use App\Models\Order;
use App\Print\Contracts\PrintProvider;
use App\Print\Contracts\SubmitOrderResult;
use App\Print\PrintConfig;

class FinerWorksProvider implements PrintProvider
{
    public function supports(Order $order): bool
    {
        $opts = PrintConfig::options('finerworks');
        return !empty($opts['api_key']) && !empty(PrintConfig::endpoint('finerworks'));
    }

    public function submit(Order $order): SubmitOrderResult
    {
        // TODO: Implement FinerWorks API submission
        $endpoint = PrintConfig::endpoint('finerworks');
        $external = 'FINERWORKS-STUB-' . $order->id;
        $order->update([
            'status' => 'submitted',
            'external_id' => $external,
            'data' => array_merge($order->data ?? [], [
                'provider' => 'finerworks',
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

