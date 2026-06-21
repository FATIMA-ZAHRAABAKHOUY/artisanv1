<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'objet'       => $this->objet,
            'description' => $this->description,
            'statut'      => $this->statut,
            'statut_label'=> match($this->statut) {
                'ouvert'   => '🔴 Ouvert',
                'en_cours' => '🟡 En cours de traitement',
                'resolu'   => '🟢 Résolu',
                'ferme'    => '⚫ Fermé',
                default    => $this->statut,
            },
 
            // Relations
            'user'        => $this->whenLoaded('user', fn() => [
                'id'  => $this->user->id,
                'nom' => $this->user->nom_complet,
            ]),
            'livraison'   => $this->whenLoaded('livraison', fn() =>
                $this->livraison ? [
                    'id'           => $this->livraison->id,
                    'numero_suivi' => $this->livraison->numero_suivi,
                    'statut'       => $this->livraison->statut,
                ] : null
            ),
 
            'created_at'  => $this->created_at?->format('d/m/Y H:i'),
            'updated_at'  => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
