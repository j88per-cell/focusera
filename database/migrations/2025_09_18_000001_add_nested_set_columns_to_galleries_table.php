<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            // Adds parent_id, _lft, _rgt columns and indexes
            NestedSet::columns($table);
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            NestedSet::dropColumns($table);
        });
    }
};

