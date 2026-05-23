<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_templates', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived'])
                  ->default('draft')
                  ->after('is_published');
            $table->json('card_schema')->nullable()->after('status');
            $table->index('status');
        });

        DB::table('game_templates')->where('is_published', true)->update(['status' => 'published']);
        DB::table('game_templates')->where('is_published', false)->update(['status' => 'draft']);

        Schema::table('game_templates', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->json('custom_data')->nullable()->after('existing_deck_mapping');
        });
    }

    public function down(): void
    {
        Schema::table('game_templates', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('created_by');
        });

        DB::table('game_templates')->where('status', 'published')->update(['is_published' => true]);

        Schema::table('game_templates', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'card_schema']);
        });

        Schema::table('objects', function (Blueprint $table) {
            $table->dropColumn('custom_data');
        });
    }
};