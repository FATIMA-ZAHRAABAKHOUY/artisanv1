<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::selectOne("
            SELECT 1 AS ok
            FROM pg_enum e
            JOIN pg_type t ON e.enumtypid = t.oid
            WHERE t.typname = 'role_utilisateur'
              AND e.enumlabel = 'fournisseur'
            LIMIT 1
        ");

        if (! $exists) {
            DB::statement("ALTER TYPE role_utilisateur ADD VALUE 'fournisseur'");
        }

        if (Schema::hasTable('fournisseurs') && ! Schema::hasColumn('fournisseurs', 'user_id')) {
            DB::statement('ALTER TABLE fournisseurs ADD COLUMN user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_fournisseurs_user_id ON fournisseurs(user_id)');
        }
    }

    public function down(): void
    {
        // PostgreSQL ne permet pas de retirer une valeur d'enum facilement.
    }
};
