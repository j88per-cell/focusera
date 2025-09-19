<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres-safe: drop NOT NULL constraint on galleries.thumbnail
        DB::statement('ALTER TABLE galleries ALTER COLUMN thumbnail DROP NOT NULL');
    }

    public function down(): void
    {
        // Re-add NOT NULL (may fail if rows have nulls)
        DB::statement('ALTER TABLE galleries ALTER COLUMN thumbnail SET NOT NULL');
    }
};

