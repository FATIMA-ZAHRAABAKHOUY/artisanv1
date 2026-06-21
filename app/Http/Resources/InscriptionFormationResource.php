<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionFormationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'statut_inscription' => $this->statut_inscription,
            'statut_label'       => match($this->statut_inscription) {
                'en_cours'    => '📚 En cours',
                'terminee'    => '🎓 Terminée',
                'abandonnee'  => '🚪 Abandonnée',
                'suspendue'   => '⏸️ Suspendue',
                default       => $this->statut_inscription,
            },
            'progression'        => $this->progression,
            'progression_label'  => $this->progression . '%',
            'note_finale'        => $this->note_finale
                                        ? (float) $this->note_finale
                                        : null,
            'note_label'         => $this->note_finale
                                        ? $this->note_finale . '/20'
                                        : null,
 
            // Dates
            'date_inscription'   => $this->date_inscription?->format('d/m/Y'),
            'date_debut_reelle'  => $this->date_debut_reelle?->format('d/m/Y'),
            'date_fin_reelle'    => $this->date_fin_reelle?->format('d/m/Y'),
 
            // Certificat
            'certificat_url'     => $this->certificat_url,
            'a_certificat'       => ! is_null($this->certificat_url),
 
            // Flags
            'est_en_cours'       => $this->estEnCours(),
            'est_terminee'       => $this->estTerminee(),
 
            // Relations
            'formation'          => $this->whenLoaded('formation', fn() => [
                'id'        => $this->formation->id,
                'titre'     => $this->formation->titre,
                'lieu'      => $this->formation->lieu,
                'prix'      => $this->formation->prix,
                'image_url' => $this->formation->image_url,
                'date_debut'=> $this->formation->date_debut?->format('d/m/Y'),
                'date_fin'  => $this->formation->date_fin?->format('d/m/Y'),
                'artisan'   => $this->formation->relationLoaded('artisan')
                                ? $this->formation->artisan->user->nom_complet
                                : null,
            ]),
 
            'apprenant'          => $this->whenLoaded('apprenant', fn() => [
                'id'  => $this->apprenant->id,
                'nom' => $this->apprenant->nom_complet,
            ]),
        ];
    }
}
