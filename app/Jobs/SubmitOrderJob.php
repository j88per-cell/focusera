<?php

namespace App\Jobs;

use App\Models\Order;
use App\Print\ProviderRegistry;
use App\Print\PrintConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(ProviderRegistry $providers): void
    {
        $order = Order::with('items')->findOrFail($this->orderId);
        $key = PrintConfig::providerKey();
        $sandbox = PrintConfig::sandbox();

        // Persist provider and sandbox flag on the order record for traceability
        $order->provider = $key;
        $order->data = array_merge($order->data ?? [], ['sandbox' => $sandbox]);
        $order->save();
        $provider = $providers->make($key);
        if (!$provider->supports($order)) {
            Log::warning('Provider does not support order', ['provider' => $key, 'order' => $order->id]);
            return;
        }
        $result = $provider->submit($order);
        Log::info('Order submitted', [
            'order' => $order->id,
            'external_id' => $result->externalId(),
            'provider' => $key,
            'sandbox' => $sandbox,
        ]);
    }
}
