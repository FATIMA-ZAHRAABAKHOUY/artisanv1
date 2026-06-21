<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
     public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'titre'    => $this->titre,
            'message'  => $this->message,
            'data'     => $this->data,
            'is_read'  => $this->is_read,
            'icone'    => match($this->type) {
                'commande_creee'       => '🛒',
                'commande_statut'      => '📦',
                'paiement_confirme'    => '✅',
                'paiement_cree'        => '💳',
                'livraison_statut'     => '🚚',
                'livraison_assignee'   => '📬',
                'inscription_formation'=> '🎓',
                'formation_terminee'   => '🏅',
                'artisan_valide'       => '✨',
                'artisan_suspendu'     => '⚠️',
                default                => '🔔',
            },
            'date'     => $this->created_at?->format('d/m/Y H:i'),
            'date_relative' => $this->created_at?->diffForHumans(),
        ];
    }
}
