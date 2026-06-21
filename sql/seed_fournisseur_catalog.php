<?php

/**
 * Insère des produits de démo pour le fournisseur id=5 (« fourni »).
 * Usage : php sql/seed_fournisseur_catalog.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$fournisseurId = (int) ($argv[1] ?? 5);

$fournisseur = DB::table('fournisseurs')->where('id', $fournisseurId)->first();
if (! $fournisseur) {
    echo "Fournisseur id={$fournisseurId} introuvable.\n";
    exit(1);
}

echo "Fournisseur : {$fournisseur->nom} (id={$fournisseurId})\n";

$existingM = DB::table('fournisseur_materiaux')->where('fournisseur_id', $fournisseurId)->count();
$existingO = DB::table('fournisseur_outils')->where('fournisseur_id', $fournisseurId)->count();

if ($existingM > 0 || $existingO > 0) {
    echo "Catalogue déjà peuplé (matériaux={$existingM}, outils={$existingO}).\n";
    echo "Supprimez les lignes existantes ou passez un autre fournisseur_id.\n";
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/seed_fournisseur_catalog.sql');
$sql = preg_replace('/^--.*$/m', '', $sql);

try {
    DB::unprepared($sql);
    $m = DB::table('fournisseur_materiaux')->where('fournisseur_id', $fournisseurId)->count();
    $o = DB::table('fournisseur_outils')->where('fournisseur_id', $fournisseurId)->count();
    echo "OK — {$m} matériaux, {$o} outils insérés pour « {$fournisseur->nom} ».\n";
    echo "Rechargez /fournisseur/produits\n";
} catch (\Throwable $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
