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
        Schema::table('object_project', function (Blueprint $table) {
            $table->foreignId('custom_image_id')->nullable()->after('object_id')->constrained('assets')->onDelete('set null');
            $table->string('custom_text')->nullable()->after('custom_image_id');
            $table->string('custom_color')->nullable()->after('custom_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('object_project', function (Blueprint $table) {
            //
        });
    }
};
