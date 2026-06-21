<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriauFormationResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'type'        => $this->type,
            'couleur'     => $this->couleur,
            'quantite'    => (float) $this->quantite,
            'unite'       => $this->unite,
            'quantite_label' => "{$this->quantite} {$this->unite}",
            'description' => $this->description,
            'est_fourni'  => $this->est_fourni,
            'image_url'   => $this->image
                                ? asset("storage/{$this->image}")
                                : null,
            'ordre'       => $this->ordre,
 
            // Suggestions fournisseurs
            'fournisseurs' => $this->whenLoaded('fournisseurs',
                fn() => $this->fournisseurs->map(fn($fm) => [
                    'fournisseur_id'         => $fm->fournisseur->id,
                    'nom'                    => $fm->fournisseur->nom,
                    'type'                   => $fm->fournisseur->type,
                    'ville'                  => $fm->fournisseur->ville,
                    'telephone'              => $fm->fournisseur->telephone,
                    'whatsapp'               => $fm->fournisseur->whatsapp,
                    'site_web'               => $fm->fournisseur->site_web,
                    'nom_produit'            => $fm->nom_produit_fournisseur,
                    'reference'              => $fm->reference_produit,
                    'prix_unitaire'          => $fm->prix_unitaire ? (float)$fm->prix_unitaire : null,
                    'unite_prix'             => $fm->unite_prix,
                    'url_produit'            => $fm->url_produit,
                    'delai_min'              => $fm->delai_livraison_min,
                    'delai_max'              => $fm->delai_livraison_max,
                    'delai_label'            => $fm->delai_livraison_min === 0
                                                ? 'Disponible sur place'
                                                : "{$fm->delai_livraison_min}-{$fm->delai_livraison_max} jours",
                    'remise_cooperative'     => $fm->fournisseur->remise_cooperative . '%',
                    'est_recommande'         => $fm->est_recommande,
                    'stock_disponible'       => $fm->stock_disponible,
                    'notes'                  => $fm->notes_apprenant,
                ])
            ),
        ];
    }
}
