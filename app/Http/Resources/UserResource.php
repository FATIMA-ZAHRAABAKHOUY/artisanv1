<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nom_complet' => $this->nom_complet,
            'nom'         => $this->nom,
            'prenom'      => $this->prenom,
            'email'       => $this->email,
            'telephone'   => $this->telephone,
            'adresse'     => $this->adresse,
            'ville'       => $this->ville,
            'code_postal' => $this->code_postal,
            'role'        => $this->role,
            'statut'      => $this->statut,
            'avatar_url'  => $this->avatar
                                ? asset("storage/{$this->avatar}")
                                : null,
            'email_verifie' => ! is_null($this->email_verified_at),
 
            // Relations conditionnelles
            'artisan'     => $this->whenLoaded('artisan',
                                fn() => new ArtisanResource($this->artisan)
                            ),
            'formateur'   => $this->whenLoaded('formateur',
                                fn() => new FormateurResource($this->formateur)
                            ),
 
            // Stats conditionnelles (admin seulement)
            'nb_commandes'  => $this->when(
                                isset($this->nb_commandes),
                                $this->nb_commandes
                               ),
            'total_depense'  => $this->when(
                                isset($this->total_depense),
                                $this->total_depense
                               ),
 
            'created_at'  => $this->created_at?->format('d/m/Y'),
            'updated_at'  => $this->updated_at?->format('d/m/Y H:i'),
        ];
   }
}