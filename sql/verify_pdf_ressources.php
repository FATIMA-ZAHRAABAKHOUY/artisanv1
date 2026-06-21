<?php

/**
 * Vérifie les 4 guides PDF + état BDD ressources.
 * Usage : php sql/verify_pdf_ressources.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$pdfDir = __DIR__ . '/../storage/app/public/formations/ressources';
$expected = [
    'guide_tapis_beni_ouarain.pdf',
    'guide_broderie_fassi.pdf',
    'guide_broderie_rbatie.pdf',
    'guide_teinture_naturelle.pdf',
];

$baseUrl = rtrim(config('app.url'), '/');
$symlink = is_link(__DIR__ . '/../public/storage') || is_dir(__DIR__ . '/../public/storage');

echo "=== VÉRIFICATION PDF RESSOURCES ===\n\n";
echo "storage:link : " . ($symlink ? "OK" : "MANQUANT — exécutez php artisan storage:link") . "\n";
echo "Dossier cible : {$pdfDir}\n\n";

$allOk = true;

foreach ($expected as $file) {
    $path = $pdfDir . DIRECTORY_SEPARATOR . $file;
    echo "Fichier : {$file}\n";

    if (! file_exists($path)) {
        echo "  ❌ ABSENT — copiez-le depuis vos Téléchargements vers :\n";
        echo "     C:\\wamp64\\www\\pfe_tissu\\storage\\app\\public\\formations\\ressources\\\n";
        $allOk = false;
        continue;
    }

    $bytes = filesize($path);
    $ko    = max(1, (int) round($bytes / 1024));

    if ($bytes === 0) {
        echo "  ❌ VIDE (0 octet) — re-téléversez le fichier original\n";
        $allOk = false;
        continue;
    }

    $header = file_get_contents($path, false, null, 0, 5);
    $isPdf  = $header === '%PDF-';

    if (! $isPdf) {
        echo "  ❌ CORROMPU — en-tête invalide (attendu %PDF-), {$bytes} octets\n";
        $allOk = false;
        continue;
    }

    echo "  ✅ Valide — {$bytes} octets (~{$ko} Ko), en-tête PDF OK\n";
    echo "  URL : {$baseUrl}/storage/formations/ressources/{$file}\n";

    DB::table('ressources_formation')
        ->where('url', 'formations/ressources/' . $file)
        ->update(['taille_ko' => $ko, 'nb_pages' => 2]);
}

echo "\n--- Placeholders PDF restants ---\n";
$placeholders = DB::table('ressources_formation')
    ->whereIn('url', ['A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF'])
    ->get(['id', 'titre', 'url']);

if ($placeholders->isEmpty()) {
    echo "✅ Aucun placeholder PDF\n";
} else {
    echo "⚠️  {$placeholders->count()} placeholder(s) restant(s)\n";
    foreach ($placeholders as $p) {
        echo "   #{$p->id} {$p->titre} → {$p->url}\n";
    }
}

echo "\n--- Lignes BDD des 4 guides ---\n";
$rows = DB::table('ressources_formation')
    ->where('url', 'like', 'formations/ressources/guide_%')
    ->get(['id', 'formation_id', 'titre', 'url', 'taille_ko', 'nb_pages', 'est_public']);

foreach ($rows as $r) {
    echo "#{$r->id} formation={$r->formation_id} | {$r->titre}\n";
    echo "  url={$r->url} | {$r->taille_ko} Ko | public=" . ($r->est_public ? 'oui' : 'non') . "\n";
}

$fassi = DB::table('ressources_formation')
    ->where('url', 'formations/ressources/guide_broderie_fassi.pdf')
    ->exists();

if (! $fassi) {
    echo "\n⚠️  guide_broderie_fassi.pdf : aucune ligne en BDD (formation Fassi absente ?)\n";
    echo "   Exécutez sql/insert_pdf_broderie_fassi.sql après création de la formation.\n";
}

echo "\n--- Vidéos YouTube en BDD (non modifiées — option A) ---\n";
$youtube = DB::table('ressources_formation')
    ->where('url', 'like', '%youtube%')
    ->orWhere('url', 'like', '%youtu.be%')
    ->get(['id', 'titre', 'url']);

foreach ($youtube as $v) {
    echo "#{$v->id} {$v->titre}\n  {$v->url}\n";
}

if ($youtube->isEmpty()) {
    echo "(aucune URL youtube.com/watch directe — liens de recherche YouTube ou chemins locaux)\n";
}

echo "\n=== RÉSULTAT : " . ($allOk ? "PDF prêts si fichiers copiés" : "ACTION REQUISE — voir ❌ ci-dessus") . " ===\n";
