<?php

namespace App\Print\Contracts;

use App\Models\Order;

interface PrintProvider
{
    /**
     * Submit an order to the provider. Implementations should set
     * external references and update Order status accordingly.
     */
    public function submit(Order $order): SubmitOrderResult;

    /**
     * Returns true if this provider can accept the current order
     * (e.g., supported products, regions, currencies).
     */
    public function supports(Order $order): bool;
}

interface SubmitOrderResult
{
    public function externalId(): ?string;
    public function raw(): array;
}

