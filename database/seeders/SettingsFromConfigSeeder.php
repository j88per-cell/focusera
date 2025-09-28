<?php

namespace Database\Seeders;

use App\Ensurers\DefaultSettingsEnsurer;
use Illuminate\Database\Seeder;

class SettingsFromConfigSeeder extends Seeder
{
    public function run(): void
    {
        (new DefaultSettingsEnsurer())->run();
    }
}
