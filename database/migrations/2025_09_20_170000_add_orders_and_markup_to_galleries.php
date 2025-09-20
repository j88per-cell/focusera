<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            if (!Schema::hasColumn('galleries', 'allow_orders')) {
                $table->boolean('allow_orders')->default(false)->after('public');
            }
            if (!Schema::hasColumn('galleries', 'markup_percent')) {
                $table->decimal('markup_percent', 8, 2)->nullable()->after('allow_orders');
            }
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            if (Schema::hasColumn('galleries', 'markup_percent')) {
                $table->dropColumn('markup_percent');
            }
            if (Schema::hasColumn('galleries', 'allow_orders')) {
                $table->dropColumn('allow_orders');
            }
        });
    }
};

