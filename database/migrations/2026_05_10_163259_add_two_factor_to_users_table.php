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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('two_factor_method', ['totp', 'email'])->nullable()->after('password');
            $table->text('two_factor_secret')->nullable()->after('two_factor_method');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
