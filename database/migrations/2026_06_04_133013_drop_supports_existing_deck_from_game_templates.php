<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_templates', function (Blueprint $table) {
            $table->dropColumn('supports_existing_deck');
        });
    }

    public function down(): void
    {
        Schema::table('game_templates', function (Blueprint $table) {
            $table->boolean('supports_existing_deck')->default(false)->after('duration_max');
        });
    }
};