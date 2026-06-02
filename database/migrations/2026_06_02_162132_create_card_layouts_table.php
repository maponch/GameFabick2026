<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('schema')->nullable();
            $table->timestamps();
        });

        Schema::table('game_templates', function (Blueprint $table) {
            $table->string('card_layout')->nullable()->after('card_schema');
            $table->index('card_layout');
        });
    }

    public function down(): void
    {
        Schema::table('game_templates', function (Blueprint $table) {
            $table->dropIndex(['card_layout']);
            $table->dropColumn('card_layout');
        });

        Schema::dropIfExists('card_layouts');
    }
};