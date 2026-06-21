<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Database: " . config('database.connections.pgsql.database') . "\n\n";

$exists = DB::selectOne("
    SELECT 1 AS ok FROM pg_enum e
    JOIN pg_type t ON e.enumtypid = t.oid
    WHERE t.typname = 'role_utilisateur' AND e.enumlabel = 'formateur'
");

if ($exists) {
    echo "OK — role 'formateur' déjà présent dans role_utilisateur.\n";
    exit(0);
}

try {
    DB::unprepared(file_get_contents(__DIR__ . '/add_formateur_role.sql'));
    echo "OK — role 'formateur' ajouté.\n";
} catch (\Throwable $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
