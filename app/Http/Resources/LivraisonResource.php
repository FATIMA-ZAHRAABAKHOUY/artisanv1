<?php

namespace App\Http\Resources;

use App\Models\Livraison;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LivraisonResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'commande_id'   => $this->commande_id,
            'statut'        => $this->statut,
            'statut_label'  => match($this->statut) {
                Livraison::STATUT_ASSIGNEE   => '📦 À préparer',
                Livraison::STATUT_EN_TRANSIT => '🚚 En route',
                Livraison::STATUT_LIVREE     => '✅ Livrée',
                Livraison::STATUT_ECHOUEE    => '↩️ Retournée',
                default                      => Livraison::statutLabel($this->statut),
            },
 
            // Tracking
            'numero_suivi'          => $this->numero_suivi,
            'transporteur'          => $this->transporteur,
 
            // Adresse
            'adresse_livraison'     => $this->adresse_livraison,
            'ville'                 => $this->ville,
            'region'                => $this->region,
            'code_postal'           => $this->code_postal,
            'telephone_recepteur'   => $this->telephone_recepteur,
 
            // Dates
            'date_expedition'       => $this->date_expedition?->format('d/m/Y H:i'),
            'date_livraison_prevue' => $this->date_livraison_prevue?->format('d/m/Y'),
            'date_livraison_reelle' => $this->date_livraison_reelle?->format('d/m/Y H:i'),
 
            // Frais
            'frais_livraison'       => (float) $this->frais_livraison,
 
            // Preuve
            'preuve_livraison_url'  => $this->preuve_livraison_url
                                        ? asset("storage/{$this->preuve_livraison_url}")
                                        : null,
 
            // Flags
            'est_livree'            => $this->estLivree(),
            'peut_changer_statut'   => $this->peutChangerStatut(),
 
            // Notes
            'notes'                 => $this->notes,
 
            // Relations
            'livreur'               => $this->whenLoaded('livreur', fn() =>
                                        $this->livreur ? [
                                            'id'        => $this->livreur->id,
                                            'nom'       => $this->livreur->nom_complet,
                                            'telephone' => $this->livreur->telephone,
                                        ] : null
                                       ),
 
            'commande'              => $this->whenLoaded('commande', fn() => [
                'id'       => $this->commande->id,
                'statut'   => $this->commande->statut,
                'total_ttc'=> $this->commande->total_ttc,
                'client'   => $this->commande->relationLoaded('client') ? [
                    'nom'       => $this->commande->client->nom_complet,
                    'telephone' => $this->commande->client->telephone,
                ] : null,
            ]),
 
            'historique'            => $this->whenLoaded('historique',
                                        fn() => LivraisonHistoriqueResource::collection($this->historique)
                                       ),
 
            'created_at'            => $this->created_at?->format('d/m/Y H:i'),
            'updated_at'            => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
