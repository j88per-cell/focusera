<?php

namespace App\Providers;

use App\Print\ProviderRegistry;
use Illuminate\Support\ServiceProvider;

class PrintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProviderRegistry::class, function () {
            $map = config('print.providers', []);
            return new ProviderRegistry($map);
        });
    }
}

