<?php

/**
 * Génère le PDF « Guide des matériaux et outils nécessaires » (broderie rbatie).
 * Usage : php sql/generate_guide_broderie_rbatie_pdf.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$outDir = __DIR__ . '/../storage/app/public/formations/ressources';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$outFile = $outDir . '/guide_broderie_rbatie.pdf';

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 28mm 22mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #2c2c2c; line-height: 1.55; }
  h1 { font-size: 20pt; color: #8b6914; margin: 0 0 6px; border-bottom: 2px solid #c8913a; padding-bottom: 8px; }
  h2 { font-size: 13pt; color: #4a3728; margin: 18px 0 8px; }
  .subtitle { font-size: 10pt; color: #666; margin-bottom: 20px; }
  .badge { display: inline-block; background: #f5efe6; border: 1px solid #d4c4a8; padding: 3px 10px;
           border-radius: 12px; font-size: 9pt; color: #8b6914; margin-bottom: 14px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0 16px; font-size: 10pt; }
  th { background: #c8913a; color: white; text-align: left; padding: 8px 10px; }
  td { border: 1px solid #ddd; padding: 7px 10px; vertical-align: top; }
  tr:nth-child(even) td { background: #faf7f2; }
  ul { margin: 6px 0 12px 18px; padding: 0; }
  li { margin-bottom: 5px; }
  .note { background: #fff8e7; border-left: 4px solid #c8913a; padding: 10px 14px; font-size: 10pt; margin-top: 14px; }
  .footer { margin-top: 24px; font-size: 9pt; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
  .page-break { page-break-before: always; }
  .check { color: #2d6a4f; font-weight: bold; }
</style>
</head>
<body>

<h1>Guide des matériaux et outils nécessaires</h1>
<p class="subtitle">Formation « Broderie Rbatie — Point compté traditionnel » · Coopérative Tissu Artisanal</p>
<span class="badge">Document pédagogique · 2 pages</span>

<p>Ce guide recense l’ensemble du matériel requis pour suivre la formation en broderie rbatie (Rabat).
Les matériaux marqués <strong>À apporter</strong> doivent être préparés avant la première séance.
L’artisan formatrice peut fournir certains éléments sur place (voir colonne « Fourni »).</p>

<h2>1. Matériaux textiles</h2>
<table>
  <tr>
    <th>Matériau</th>
    <th>Spécification</th>
    <th>Quantité</th>
    <th>Fourni</th>
  </tr>
  <tr>
    <td><strong>Toile de lin</strong></td>
    <td>Lin écrue, fil 16–18 count/cm, tissage serré pour point compté</td>
    <td>50 × 50 cm minimum</td>
    <td>Non</td>
  </tr>
  <tr>
    <td><strong>Fils de soie</strong></td>
    <td>Soie moulinée n°25 — rouge bordeaux, vert émeraude, or, noir, blanc cassé</td>
    <td>1 écheveau par couleur</td>
    <td>Partiel</td>
  </tr>
  <tr>
    <td><strong>Fil de couture</strong></td>
    <td>Coton mercerisé beige pour bordures et finitions</td>
    <td>1 bobine</td>
    <td>Oui</td>
  </tr>
  <tr>
    <td><strong>Entoilage léger</strong></td>
    <td>Pour stabiliser le lin lors du montage sur tambour (optionnel)</td>
    <td>1 feuille A4</td>
    <td>Non</td>
  </tr>
</table>

<h2>2. Outils de broderie</h2>
<table>
  <tr>
    <th>Outil</th>
    <th>Usage</th>
    <th>Quantité</th>
    <th>Fourni</th>
  </tr>
  <tr>
    <td><strong>Aiguilles à broder</strong></td>
    <td>Pointes longues n°7 à n°9, œil large pour fil de soie</td>
    <td>3 aiguilles</td>
    <td>Oui</td>
  </tr>
  <tr>
    <td><strong>Tambour à broder</strong></td>
    <td>Diamètre 20–25 cm, serrage uniforme du tissu</td>
    <td>1</td>
    <td>Oui (prêt sur place)</td>
  </tr>
  <tr>
    <td><strong>Ciseaux de précision</strong></td>
    <td>Coupe fils et finitions nettes</td>
    <td>1 paire</td>
    <td>Non</td>
  </tr>
  <tr>
    <td><strong>Dé à cannette / enfile-aiguille</strong></td>
    <td>Enfilage rapide des fils de soie</td>
    <td>1</td>
    <td>Oui</td>
  </tr>
  <tr>
    <td><strong>Crayon effaçable / feutre textile</strong></td>
    <td>Report du motif sur la toile (grille point compté)</td>
    <td>1</td>
    <td>Oui</td>
  </tr>
</table>

<div class="note">
  <strong>Conseil :</strong> Préparez vos fils de soie en les coupant en brins de 50 cm maximum
  et en les séparant en 2 ou 3 brins selon l’épaisseur souhaitée. Conservez-les à l’abri
  de l’humidité dans une boîte ou un organiser.
</div>

<div class="page-break"></div>

<h1>Checklist avant la formation</h1>
<p class="subtitle">Cochez chaque élément préparé · Session Broderie Rbatie</p>

<h2>3. Liste de contrôle — À apporter</h2>
<ul>
  <li><span class="check">☐</span> Toile de lin écrue (50 × 50 cm minimum)</li>
  <li><span class="check">☐</span> Fils de soie : rouge bordeaux, vert, or, noir, blanc</li>
  <li><span class="check">☐</span> Ciseaux de précision personnels</li>
  <li><span class="check">☐</span> Carnet pour notes et échantillons de points</li>
  <li><span class="check">☐</span> Lunettes si nécessaire (travail de près)</li>
</ul>

<h2>4. Fourni par l’atelier (sur place)</h2>
<ul>
  <li>Tambour à broder, aiguilles, enfile-aiguille</li>
  <li>Feutre textile et grille de report de motif</li>
  <li>Fiches techniques du point compté rbati</li>
  <li>Modèles traditionnels de motifs rbatis (géométriques et floraux)</li>
</ul>

<h2>5. Entretien et conservation</h2>
<p>Après chaque séance :</p>
<ul>
  <li>Enroulez l’ouvrage autour du tambour sans plier les points brodés.</li>
  <li>Rangez les fils par couleur dans des sachets ou un organiser.</li>
  <li>Ne lavez pas le lin brodé avant la fin du motif — un lavage à la main tiède
      avec savon de Marseille suffira pour la pièce finie.</li>
</ul>

<h2>6. Ressources complémentaires</h2>
<p>Consultez également sur la plateforme Tissu Artisanal :</p>
<ul>
  <li>Vidéo « Introduction à la broderie rbatie »</li>
  <li>Galerie de pièces finies (exemples de sessions précédentes)</li>
  <li>Guide PDF des motifs traditionnels rbatis (ressource avancée)</li>
</ul>

<div class="note">
  <strong>Contact :</strong> Pour toute question sur le matériel, contactez votre artisan formatrice
  via l’espace formation ou la coopérative Tissu Artisanal avant le début du cours.
</div>

<div class="footer">
  Tissu Artisanal · Coopérative de l’artisanat textile marocain · Document généré pour la formation Broderie Rbatie
</div>

</body>
</html>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

file_put_contents($outFile, $dompdf->output());

$bytes = filesize($outFile);
$ko    = max(1, (int) round($bytes / 1024));
$pages = 2;

echo "PDF généré : {$outFile}\n";
echo "Taille : {$bytes} octets (~{$ko} Ko)\n";

DB::table('ressources_formation')
    ->where('url', 'formations/ressources/guide_broderie_rbatie.pdf')
    ->update([
        'nb_pages'  => $pages,
        'taille_ko' => $ko,
        'type'      => 'document_pdf',
    ]);

echo "Base de données mise à jour (ressource guide_broderie_rbatie.pdf).\n";
