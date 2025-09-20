<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_access_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash');
            $table->string('label')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['gallery_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_access_codes');
    }
};

