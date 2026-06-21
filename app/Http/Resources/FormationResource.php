<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormationResource extends JsonResource
{ public function toArray(Request $request): array
    {
        // Durée totale formation
        $dureeTotal = $this->relationLoaded('etapes')
            ? $this->etapes->sum('duree_minutes')
            : null;
 
        return [
            'id'                 => $this->id,
            'titre'              => $this->titre,
            'description'        => $this->description,
            'date_debut'         => $this->date_debut?->format('d/m/Y'),
            'date_fin'           => $this->date_fin?->format('d/m/Y'),
            'duree_jours'        => $this->date_debut && $this->date_fin
                                        ? $this->date_debut->diffInDays($this->date_fin) + 1
                                        : null,
            'prix'               => (float) $this->prix,
            'est_gratuite'       => $this->prix == 0,
            'places_max'         => $this->places_max,
            'places_disponibles' => $this->placesDisponibles(),
            'est_complete'       => $this->estComplete(),
            'lieu'               => $this->lieu,
            'image_url'          => $this->image_url,
            'is_active'          => $this->is_active,
 
            // Durée totale (si étapes chargées)
            'duree_totale_minutes' => $dureeTotal,
            'duree_totale_label'   => $dureeTotal
                                        ? $this->formatDuree($dureeTotal)
                                        : null,
 
            // Artisan
            'artisan'            => $this->whenLoaded('artisan', fn() => [
                'id'           => $this->artisan->id,
                'nom'          => $this->artisan->user->nom_complet,
                'specialite'   => $this->artisan->specialite,
                'note_moyenne' => $this->artisan->note_moyenne,
                'avatar_url'   => $this->artisan->user->avatar_url,
            ]),
 
            // Formateurs
            'formateurs'         => $this->whenLoaded('formateurs',
                fn() => $this->formateurs->map(fn($fm) => [
                    'id'          => $fm->id,
                    'nom'         => $fm->user->nom_complet,
                    'role'        => $fm->pivot->role,
                    'role_label'  => match($fm->pivot->role) {
                        'principal'   => '👨‍🏫 Formateur principal',
                        'assistant'   => '🤝 Assistant',
                        'intervenant' => '🎤 Intervenant',
                        default       => $fm->pivot->role,
                    },
                    'specialite'  => $fm->specialite,
                    'est_externe' => $fm->est_externe,
                    'organisme'   => $fm->when($fm->est_externe, $fm->organisme),
                ])
            ),
 
            // Contenu pédagogique
            'etapes'             => $this->whenLoaded('etapes',
                fn() => EtapeFormationResource::collection($this->etapes)
            ),
            'materiaux'          => $this->whenLoaded('materiaux',
                fn() => MateriauFormationResource::collection($this->materiaux)
            ),
            'outils'             => $this->whenLoaded('outils',
                fn() => OutilFormationResource::collection($this->outils)
            ),
            'ressources'         => $this->whenLoaded('ressourcesPubliques',
                fn() => RessourceFormationResource::collection($this->ressourcesPubliques)
            ),
 
            // Inscriptions (artisan/admin)
            'inscriptions'       => $this->whenLoaded('inscriptions',
                fn() => InscriptionFormationResource::collection($this->inscriptions)
            ),
 
            // Counts
            'nb_inscrits'        => $this->whenCounted('inscriptions'),
 
            'created_at'         => $this->created_at?->format('d/m/Y'),
            'updated_at'         => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
 
    private function formatDuree(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h{$m}min";
        if ($h > 0)            return "{$h}h";
        return "{$m}min";
    }
}
