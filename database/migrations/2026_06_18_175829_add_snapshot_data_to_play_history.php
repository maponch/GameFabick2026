<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('play_history', function (Blueprint $table) {
            $table->json('snapshot_data')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('play_history', function (Blueprint $table) {
            $table->dropColumn('snapshot_data');
        });
    }
};