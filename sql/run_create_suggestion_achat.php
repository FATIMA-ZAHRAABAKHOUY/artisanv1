<?php

/**
 * Crée la table suggestion_achat si absente.
 * Usage : php sql/run_create_suggestion_achat.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Database: " . config('database.connections.pgsql.database') . "\n\n";

if (Schema::hasTable('suggestion_achat')) {
    echo "suggestion_achat already exists (" . DB::table('suggestion_achat')->count() . " rows).\n";
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/create_suggestion_achat.sql');

try {
    DB::unprepared($sql);
    echo "Table suggestion_achat created successfully.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
