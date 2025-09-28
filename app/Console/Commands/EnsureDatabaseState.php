<?php

namespace App\Console\Commands;

use App\Ensurers\DefaultSettingsEnsurer;
use Illuminate\Console\Command;

class EnsureDatabaseState extends Command
{
    protected $signature = 'db:ensure {--only= : Comma separated list of ensure groups to run (available: settings)}';

    protected $description = 'Ensure required baseline records exist in the database.';

    public function handle(): int
    {
        $requestedGroups = $this->parseRequestedGroups();
        $ran = false;

        if ($requestedGroups->contains('settings')) {
            $ran = true;
            (new DefaultSettingsEnsurer())->run();
            $this->info('Default settings ensured.');
        }

        $unknown = $requestedGroups->reject(fn ($group) => in_array($group, ['settings'], true));
        if ($unknown->isNotEmpty()) {
            $this->warn('Unknown ensure groups: '. $unknown->implode(', '));
        }

        if (!$ran && $unknown->isEmpty()) {
            $this->warn('Nothing to ensure.');
        }

        return self::SUCCESS;
    }

    protected function parseRequestedGroups()
    {
        $raw = $this->option('only');

        if (is_string($raw) && trim($raw) !== '') {
            $parts = array_filter(array_map('trim', explode(',', $raw)));
            return collect($parts)->unique()->values();
        }

        return collect(['settings']);
    }
}
