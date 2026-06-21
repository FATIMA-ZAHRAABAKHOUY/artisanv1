<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutilFormationResource extends JsonResource
{
     public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'quantite'    => $this->quantite,
            'est_fourni'  => $this->est_fourni,
            'image_url'   => $this->image
                                ? asset("storage/{$this->image}")
                                : null,
            'lien_achat'  => $this->lien_achat,
            'ordre'       => $this->ordre,
 
            // Suggestions fournisseurs
            'fournisseurs' => $this->whenLoaded('fournisseurs',
                fn() => $this->fournisseurs->map(fn($fo) => [
                    'fournisseur_id'     => $fo->fournisseur->id,
                    'nom'                => $fo->fournisseur->nom,
                    'type'               => $fo->fournisseur->type,
                    'ville'              => $fo->fournisseur->ville,
                    'telephone'          => $fo->fournisseur->telephone,
                    'whatsapp'           => $fo->fournisseur->whatsapp,
                    'site_web'           => $fo->fournisseur->site_web,
                    'nom_produit'        => $fo->nom_produit_fournisseur,
                    'reference'          => $fo->reference_produit,
                    'prix_unitaire'      => $fo->prix_unitaire ? (float)$fo->prix_unitaire : null,
                    'unite_prix'         => $fo->unite_prix,
                    'url_produit'        => $fo->url_produit,
                    'delai_min'          => $fo->delai_livraison_min,
                    'delai_max'          => $fo->delai_livraison_max,
                    'delai_label'        => $fo->delai_livraison_min === 0
                                            ? 'Disponible sur place'
                                            : "{$fo->delai_livraison_min}-{$fo->delai_livraison_max} jours",
                    'remise_cooperative' => $fo->fournisseur->remise_cooperative . '%',
                    'est_recommande'     => $fo->est_recommande,
                    'stock_disponible'   => $fo->stock_disponible,
                    'notes'              => $fo->notes_apprenant,
                ])
            ),
        ];
    }
 }