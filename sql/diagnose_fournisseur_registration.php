<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== fournisseurs columns ===\n";
print_r(Schema::getColumnListing('fournisseurs'));

echo "\n=== Orphaned fournisseur users (no fournisseurs row) ===\n";
$orphans = DB::select("
    SELECT u.id, u.email, u.role, u.created_at
    FROM users u
    LEFT JOIN fournisseurs f ON f.user_id = u.id
    WHERE u.role = 'fournisseur' AND f.id IS NULL
    ORDER BY u.created_at DESC
    LIMIT 10
");
print_r($orphans);

echo "\n=== Recent fournisseur users with join ===\n";
$recent = DB::select("
    SELECT u.id, u.email, u.role, f.id as fournisseur_id, f.nom, f.statut
    FROM users u
    LEFT JOIN fournisseurs f ON f.user_id = u.id
    WHERE u.role = 'fournisseur'
    ORDER BY u.created_at DESC
    LIMIT 5
");
print_r($recent);
