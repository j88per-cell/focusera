<?php

use App\Ensurers\DefaultSettingsEnsurer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        (new DefaultSettingsEnsurer())->run();
    }

    public function down(): void
    {
        // No-op: preserving seeded defaults on rollback avoids removing live configuration.
    }
};

