<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->string('exif_visibility')->default('all'); // all | none | custom
            $table->json('exif_fields')->nullable(); // when custom, list of fields to show
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['exif_visibility', 'exif_fields']);
        });
    }
};

