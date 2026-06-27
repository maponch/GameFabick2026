<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->morphs('reportable');

            $table->enum('reason_code', [
                'spam',
                'inappropriate',
                'low_quality',
                'copyright',
                'other',
            ]);
            $table->text('reason_text')->nullable();

            $table->enum('status', ['pending', 'reviewed', 'dismissed'])
                ->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->unique(
                ['reporter_id', 'reportable_type', 'reportable_id'],
                'reports_unique_per_reporter'
            );
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};