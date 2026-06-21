<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'statut' => $this->statut,
 
            // Libellé lisible du statut
            'statut_label' => match($this->statut) {
                'pending'    => '⏳ En attente de confirmation',
                'confirmed'  => '✅ Confirmée',
                'processing' => '📦 En préparation',
                'shipped'    => '🚚 Expédiée',
                'delivered'  => '✔️ Livrée',
                'cancelled'  => '❌ Annulée',
                default      => $this->statut,
            },
 
            // Montants
            'total_ht'    => (float) $this->total_ht,
            'tva'         => (float) $this->tva,
            'tva_pct'     => round($this->tva * 100) . '%',
            'total_ttc'   => (float) $this->total_ttc,
 
            // Livraison
            'adresse_livraison' => $this->adresse_livraison,
            'ville'             => $this->ville,
            'code_postal'       => $this->code_postal,
            'notes'             => $this->notes,
 
            // Drapeaux utiles pour le frontend
            'peut_annuler'  => $this->peutEtreAnnulee(),
            'est_payee'     => $this->relationLoaded('paiement')
                                ? $this->paiement?->estPaye()
                                : null,
            'est_livree'    => $this->statut === 'delivered',
 
            // Dates
            'created_at'    => $this->created_at?->format('d/m/Y H:i'),
            'updated_at'    => $this->updated_at?->format('d/m/Y H:i'),
 
            // Relations
            'client'        => $this->whenLoaded('client', fn() => [
                'id'        => $this->client->id,
                'nom'       => $this->client->nom_complet,
                'telephone' => $this->client->telephone,
                'email'     => $this->client->email,
            ]),
 
            'lignes'        => $this->whenLoaded('lignes',
                                fn() => LigneCommandeResource::collection($this->lignes)
                               ),
 
            'paiement'      => $this->whenLoaded('paiement',
                                fn() => $this->paiement
                                        ? new PaiementResource($this->paiement)
                                        : null
                               ),
 
            'livraison'     => $this->whenLoaded('livraison',
                                fn() => $this->livraison
                                        ? new LivraisonResource($this->livraison)
                                        : null
                               ),
 
            // Résumé des lignes (sans charger tous les détails)
            'nb_articles'   => $this->whenLoaded('lignes',
                                fn() => $this->lignes->sum('quantite')
                               ),
        ];
    }
}
