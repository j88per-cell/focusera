<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('attribution')->nullable()->after('description');
            $table->text('notes')->nullable()->after('attribution');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->string('attribution')->nullable()->after('description');
            $table->text('notes')->nullable()->after('attribution');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['attribution', 'notes']);
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['attribution', 'notes']);
        });
    }
};
