<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Database: " . config('database.connections.pgsql.database') . "\n";
echo "Host: " . config('database.connections.pgsql.host') . "\n\n";

$column = DB::selectOne("
    SELECT column_name, udt_name, data_type
    FROM information_schema.columns
    WHERE table_schema = 'public'
      AND table_name = 'users'
      AND column_name = 'role'
");

echo "users.role column:\n";
print_r($column);

$enums = DB::select("
    SELECT e.enumlabel
    FROM pg_enum e
    JOIN pg_type t ON e.enumtypid = t.oid
    WHERE t.typname = ?
    ORDER BY e.enumsortorder
", [$column->udt_name ?? 'role_utilisateur']);

echo "\nEnum values for type '" . ($column->udt_name ?? '?') . "':\n";
foreach ($enums as $e) {
    echo "  - {$e->enumlabel}\n";
}

$hasFournisseur = collect($enums)->contains(fn ($e) => $e->enumlabel === 'fournisseur');
echo "\nfournisseur present: " . ($hasFournisseur ? 'YES' : 'NO') . "\n";

if (! $hasFournisseur) {
    $typeName = $column->udt_name ?? 'role_utilisateur';
    echo "\nAdding 'fournisseur' to {$typeName}...\n";
    DB::statement("ALTER TYPE {$typeName} ADD VALUE 'fournisseur'");
    echo "Done.\n";
}
