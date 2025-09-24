<?php

namespace App\Print;

use App\Print\Contracts\PrintProvider;
use InvalidArgumentException;

class ProviderRegistry
{
    /** @var array<string,class-string<PrintProvider>> */
    protected array $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function has(string $key): bool
    {
        return isset($this->map[$key]);
    }

    public function keys(): array
    {
        return array_keys($this->map);
    }

    public function make(string $key): PrintProvider
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException("Unknown print provider: {$key}");
        }
        $cls = $this->map[$key];
        return app($cls);
    }
}

