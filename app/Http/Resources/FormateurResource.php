<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormateurResource extends JsonResource
{public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'biographie'        => $this->biographie,
            'specialite'        => $this->specialite,
            'diplomes'          => $this->diplomes,
            'langues'           => $this->langues,
            'experience_annees' => $this->experience_annees,
            'est_externe'       => $this->est_externe,
            'organisme'         => $this->when($this->est_externe, $this->organisme),
            'is_disponible'     => $this->is_disponible,
 
            // Tarif — admin seulement
            'tarif_journee'     => $this->when(
                                    $request->user()?->isAdmin(),
                                    $this->tarif_journee
                                   ),
 
            'user'              => $this->whenLoaded('user', fn() => [
                'nom'       => $this->user->nom_complet,
                'avatar_url'=> $this->user->avatar_url,
            ]),
            'artisan'           => $this->whenLoaded('artisan',
                                    fn() => $this->artisan
                                            ? ['id' => $this->artisan->id,
                                               'specialite' => $this->artisan->specialite]
                                            : null
                                   ),
        ];
    } 
}
