<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('ressources_formation as r')
    ->join('formations as f', 'f.id', '=', 'r.formation_id')
    ->select('f.id as fid', 'f.titre', 'r.id', 'r.type', 'r.titre as res_titre', 'r.url', 'r.est_public', 'r.nb_pages')
    ->orderBy('f.id')
    ->orderBy('r.ordre')
    ->get();

echo count($rows) . " ressource(s)\n\n";
foreach ($rows as $r) {
    echo "Formation #{$r->fid}: {$r->titre}\n";
    echo "  [{$r->type}] {$r->res_titre}\n";
    echo "  url: {$r->url}\n";
    echo "  public: " . ($r->est_public ? 'oui' : 'non') . ", pages: {$r->nb_pages}\n\n";
}
