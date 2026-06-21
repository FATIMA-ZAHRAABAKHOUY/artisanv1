<?php

/**
 * Remplace les placeholders PDF (A_REMPLACER_PDF_UPLOAD) par les chemins locaux.
 * Prérequis : copier les 4 PDF dans storage/app/public/formations/ressources/
 * Usage : php sql/run_update_pdf_ressources.php
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

echo "Database: " . config('database.connections.pgsql.database') . "\n\n";

echo "Vérification des fichiers PDF...\n";
foreach ($expected as $file) {
    $path = $pdfDir . DIRECTORY_SEPARATOR . $file;
    if (file_exists($path)) {
        $ko = (int) round(filesize($path) / 1024);
        echo "  OK  {$file} ({$ko} Ko)\n";
    } else {
        echo "  MANQUANT  {$file}\n";
        echo "  → Copiez-le dans : {$pdfDir}\n";
    }
}

$placeholders = DB::table('ressources_formation')
    ->whereIn('url', ['A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF'])
    ->count();

echo "\nPlaceholders restants avant exécution : {$placeholders}\n";

if ($placeholders === 0) {
    echo "Aucun placeholder à mettre à jour.\n";
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/update_pdf_ressources.sql');

try {
    DB::unprepared($sql);
    echo "\nScript SQL exécuté avec succès.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

$remaining = DB::table('ressources_formation')
    ->whereIn('url', ['A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF'])
    ->count();

echo "Placeholders restants après exécution : {$remaining}\n";

if ($remaining > 0) {
    echo "ATTENTION : certains placeholders n'ont pas été remplacés (formation introuvable ?).\n";
    exit(1);
}

echo "\nRessources finales :\n";
$rows = DB::table('ressources_formation as r')
    ->join('formations as f', 'f.id', '=', 'r.formation_id')
    ->select('f.titre as formation', 'r.type', 'r.titre as ressource', 'r.url', 'r.est_public')
    ->orderBy('f.id')
    ->orderBy('r.ordre')
    ->get();

foreach ($rows as $row) {
    echo "  [{$row->formation}] {$row->type} — {$row->ressource} → {$row->url}\n";
}

echo "\nURLs publiques PDF (exemple) :\n";
$base = rtrim(config('app.url'), '/');
foreach ($rows as $row) {
    if ($row->type === 'document_pdf' && ! str_starts_with($row->url, 'http')) {
        $path = str_starts_with($row->url, '/storage/')
            ? ltrim(substr($row->url, strlen('/storage/')), '/')
            : $row->url;
        echo "  {$base}/storage/{$path}\n";
    }
}

// Mettre à jour taille_ko depuis les fichiers réellement présents
foreach ($expected as $file) {
    $path = $pdfDir . DIRECTORY_SEPARATOR . $file;
    if (! file_exists($path)) {
        continue;
    }
    $ko = max(1, (int) round(filesize($path) / 1024));
    $dbPath = 'formations/ressources/' . $file;
    DB::table('ressources_formation')
        ->where('url', $dbPath)
        ->update(['taille_ko' => $ko]);
    echo "\nTaille réelle enregistrée pour {$file} : {$ko} Ko\n";
}
