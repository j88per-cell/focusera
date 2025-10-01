<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE galleries ALTER COLUMN thumbnail DROP NOT NULL');
        } else {
            DB::statement('ALTER TABLE galleries MODIFY COLUMN thumbnail VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE galleries ALTER COLUMN thumbnail SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE galleries MODIFY COLUMN thumbnail VARCHAR(255) NOT NULL');
        }
    }
};
