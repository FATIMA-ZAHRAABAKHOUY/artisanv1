<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtisanResource extends JsonResource
{public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'specialite'        => $this->specialite,
            'bio'               => $this->bio,
            'experience_annees' => $this->experience_annees,
            'region'            => $this->whenLoaded('user', fn() => $this->user->ville),
            'note_moyenne'      => $this->note_moyenne,
            'statut'            => $this->statut,
            'is_verified'       => $this->is_verified,
            'date_adhesion'     => $this->date_adhesion?->format('d/m/Y'),
 
            // Champs sensibles — artisan connecté ou admin uniquement
            'cin'               => $this->when(
                                    $request->user()?->id === $this->user_id
                                    || $request->user()?->isAdmin(),
                                    $this->cin
                                   ),
            'rib'               => $this->when(
                                    $request->user()?->id === $this->user_id
                                    || $request->user()?->isAdmin(),
                                    $this->rib
                                   ),
 
            // Relations
            'user'              => $this->whenLoaded('user', fn() => [
                'nom'        => $this->user->nom_complet,
                'email'      => $this->user->email,
                'telephone'  => $this->user->telephone,
                'avatar_url' => $this->user->avatar_url,
            ]),
            'produits'          => $this->whenLoaded('produits',
                                    fn() => ProduitResource::collection($this->produits)
                                   ),
            'fournisseurs'      => $this->whenLoaded('fournisseurs',
                                    fn() => $this->fournisseurs->map(fn($f) => [
                                        'id'            => $f->id,
                                        'nom'           => $f->nom,
                                        'type'          => $f->type,
                                        'est_principal' => $f->pivot->est_principal,
                                    ])
                                   ),
 
            // Counts
            'nb_produits'       => $this->whenCounted('produits'),
            'nb_formations'     => $this->whenCounted('formations'),
        ];
    }
 }