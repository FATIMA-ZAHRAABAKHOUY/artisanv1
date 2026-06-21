<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

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
    echo "Added 'fournisseur' to role_utilisateur enum.\n";
} else {
    echo "'fournisseur' already exists in role_utilisateur enum.\n";
}

DB::statement('ALTER TABLE fournisseurs ADD COLUMN IF NOT EXISTS user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL');
DB::statement('CREATE INDEX IF NOT EXISTS idx_fournisseurs_user_id ON fournisseurs(user_id)');

echo "fournisseurs.user_id column ensured.\n";
echo "Done.\n";
