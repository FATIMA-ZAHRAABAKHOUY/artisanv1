<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtapeFormationResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'numero_ordre'     => $this->numero_ordre,
            'titre'            => $this->titre,
            'description'      => $this->description,
            'duree_minutes'    => $this->duree_minutes,
            'duree_label'      => $this->duree_minutes
                                    ? $this->formatDuree($this->duree_minutes)
                                    : null,
            'objectif'         => $this->objectif,
            'materiaux_requis' => $this->materiaux_requis,
        ];
    }
 
    private function formatDuree(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h{$m}min";
        if ($h > 0)           return "{$h}h";
        return "{$m}min";
    }
}
