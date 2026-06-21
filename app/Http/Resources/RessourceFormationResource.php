<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RessourceFormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'type_label'     => match($this->type) {
                'video'          => '🎬 Vidéo',
                'document_pdf'   => '📄 PDF',
                'image'          => '🖼️ Image',
                'lien_externe'   => '🔗 Lien externe',
                default          => $this->type,
            },
            'titre'          => $this->titre,
            'description'    => $this->description,
            'url'            => $this->url_complete,
            'est_public'     => $this->est_public,
            'ordre'          => $this->ordre,
 
            // Spécifique vidéo
            'duree_secondes' => $this->when($this->type === 'video', $this->duree_secondes),
            'duree_label'    => $this->when(
                                    $this->type === 'video' && $this->duree_secondes,
                                    fn() => $this->formatDureeVideo($this->duree_secondes)
                                 ),
            'resolution'     => $this->when($this->type === 'video', $this->resolution),
 
            // Spécifique PDF
            'auteur'         => $this->when($this->type === 'document_pdf', $this->auteur),
            'nb_pages'       => $this->when($this->type === 'document_pdf', $this->nb_pages),
 
            // Taille
            'taille_ko'      => $this->taille_ko,
            'taille_label'   => $this->taille_ko
                                    ? $this->formatTaille($this->taille_ko)
                                    : null,
        ];
    }
 
    private function formatDureeVideo(int $secondes): string
    {
        $h = intdiv($secondes, 3600);
        $m = intdiv($secondes % 3600, 60);
        $s = $secondes % 60;
        if ($h > 0) return sprintf('%d:%02d:%02d', $h, $m, $s);
        return sprintf('%d:%02d', $m, $s);
    }
 
    private function formatTaille(int $ko): string
    {
        if ($ko < 1024) return "{$ko} Ko";
        $mb = round($ko / 1024, 1);
        return "{$mb} Mo";
    }
}
