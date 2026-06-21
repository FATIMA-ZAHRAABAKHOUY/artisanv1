<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'methode'   => $this->methode,
            'statut'    => $this->statut,
            'montant'   => (float) $this->montant,
            'reference' => $this->reference,
            'est_paye'  => $this->estPaye(),
            'paid_at'   => $this->paid_at?->format('d/m/Y H:i'),
 
            // Données gateway — admin seulement
            'gateway_data' => $this->when(
                               $request->user()?->isAdmin(),
                               $this->gateway_data
                              ),
 
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
