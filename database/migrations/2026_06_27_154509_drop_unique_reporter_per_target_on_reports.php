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
        Schema::table('reports', function (Blueprint $table) {
            $table->index('reporter_id', 'reports_reporter_id_index');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique('reports_unique_per_reporter');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unique(
                ['reporter_id', 'reportable_type', 'reportable_id'],
                'reports_unique_per_reporter'
            );
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_reporter_id_index');
        });
    }
};
