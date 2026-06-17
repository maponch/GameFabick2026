<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Élargir temporairement l'enum pour qu'il accepte les anciennes ET nouvelles valeurs
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('brouillon','draft','published','archived') NOT NULL DEFAULT 'draft'");

        // 2. Migrer les données
        DB::table('projects')->where('status', 'brouillon')->update(['status' => 'draft']);

        // 3. Restreindre l'enum aux seules nouvelles valeurs
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('brouillon','draft','published','archived') NOT NULL DEFAULT 'brouillon'");
        DB::table('projects')->where('status', 'draft')->update(['status' => 'brouillon']);
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('brouillon') NOT NULL DEFAULT 'brouillon'");
    }
};