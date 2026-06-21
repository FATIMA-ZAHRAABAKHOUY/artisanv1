<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
class FournisseurResource extends JsonResource
{public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nom'                 => $this->nom,
            'type'                => $this->type,
            'type_label'          => match($this->type) {
                'local'     => '🏪 Boutique locale',
                'national'  => '🚚 Livraison nationale',
                'en_ligne'  => '🌐 En ligne',
                default     => $this->type,
            },
            'statut'              => $this->statut,
 
            // Coordonnées
            'email'               => $this->email,
            'telephone'           => $this->telephone,
            'whatsapp'            => $this->whatsapp,
            'adresse'             => $this->adresse,
            'ville'               => $this->ville,
            'region'              => $this->region,
            'code_postal'         => $this->code_postal,
            'site_web'            => $this->site_web,
            'logo_url'            => $this->logo_url,
            'description'         => $this->description,
 
            // Conditions commerciales
            'remise_cooperative'  => (float) $this->remise_cooperative,
            'remise_label'        => $this->remise_cooperative > 0
                                        ? $this->remise_cooperative . '% pour les membres'
                                        : 'Aucune remise',
            'delai_livraison_min' => $this->delai_livraison_min,
            'delai_livraison_max' => $this->delai_livraison_max,
            'delai_label'         => $this->delai_livraison_min === 0
                                        ? 'Disponible sur place'
                                        : "{$this->delai_livraison_min} - {$this->delai_livraison_max} jours",
            'note_moyenne'        => (float) $this->note_moyenne,
 
            // Spécialités
            'specialites'         => $this->whenLoaded('specialites',
                                        fn() => $this->specialites->pluck('specialite')
                                     ),
 
            // Artisans partenaires (admin seulement)
            'artisans_partenaires' => $this->when(
                $request->user()?->isAdmin(),
                $this->whenLoaded('artisans', fn() =>
                    $this->artisans->map(fn($a) => [
                        'id'            => $a->id,
                        'nom'           => $a->user->nom_complet,
                        'specialite'    => $a->specialite,
                        'est_principal' => $a->pivot->est_principal,
                    ])
                )
            ),
 
            'created_at'          => $this->created_at?->format('d/m/Y'),
        ];
    }
}
