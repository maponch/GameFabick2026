<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedInteger('quantity')->default(1)->after('description');
            $table->string('default_color')->nullable()->after('quantity');
            $table->string('default_image_path')->nullable()->after('default_color');
            $table->json('existing_deck_mapping')->nullable()->after('default_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->dropColumn(['description', 'quantity', 'default_color', 'default_image_path', 'existing_deck_mapping']);
        });
    }
};
