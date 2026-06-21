<?php

namespace App\Http\Resources;

use App\Models\Livraison;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LivraisonHistoriqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'statut'       => $this->statut,
            'statut_label' => Livraison::statutLabel($this->statut),
            'commentaire'  => $this->commentaire,
            'localisation' => $this->localisation,
            'par'          => $this->whenLoaded('modifiePar',
                                fn() => $this->modifiePar?->nom_complet ?? 'Système'
                             ),
            'date'         => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
