<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

foreach (['fournisseur_materiaux', 'fournisseur_outils'] as $t) {
    echo "=== $t ===\n";
    $cols = DB::select("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = ?
        ORDER BY ordinal_position
    ", [$t]);
    foreach ($cols as $c) {
        echo "  {$c->column_name} ({$c->data_type}) null={$c->is_nullable}\n";
    }
    echo "rows: " . DB::table($t)->count() . "\n\n";
}
