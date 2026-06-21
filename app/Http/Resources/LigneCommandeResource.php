<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneCommandeResource extends JsonResource
{public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'quantite'      => $this->quantite,
            'prix_unitaire' => (float) $this->prix_unitaire,
            'remise'        => (float) $this->remise,
            'remise_pct'    => round($this->remise * 100, 1) . '%',
            'sous_total'    => (float) $this->sous_total,
 
            // Produit lié
            'produit'       => $this->whenLoaded('produit', fn() => [
                'id'              => $this->produit->id,
                'nom'             => $this->produit->nom,
                'slug'            => $this->produit->slug,
                'image_principale'=> isset($this->produit->images[0])
                                        ? asset("storage/{$this->produit->images[0]}")
                                        : null,
                'prix_actuel'     => (float) $this->produit->prix,
                // prix_unitaire = prix au moment de la commande (figé)
                'artisan'         => $this->produit->relationLoaded('artisan') ? [
                    'id'  => $this->produit->artisan->id,
                    'nom' => $this->produit->artisan->user->nom_complet,
                ] : null,
            ]),
        ];
    }
}
