<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::selectOne("
            SELECT 1 AS ok
            FROM pg_enum e
            JOIN pg_type t ON e.enumtypid = t.oid
            WHERE t.typname = 'role_utilisateur'
              AND e.enumlabel = 'livreur'
            LIMIT 1
        ");

        if (! $exists) {
            DB::statement("ALTER TYPE role_utilisateur ADD VALUE 'livreur'");
        }
    }

    public function down(): void
    {
        // PostgreSQL ne permet pas de retirer une valeur d'un enum facilement.
    }
};
