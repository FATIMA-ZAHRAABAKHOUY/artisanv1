<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'note'        => $this->note,
            'commentaire' => $this->commentaire,
            'date'        => $this->created_at?->format('d/m/Y'),
            'client'      => $this->whenLoaded('client', fn() => [
                'nom'       => $this->client->nom_complet,
                'avatar_url'=> $this->client->avatar_url,
            ]),
        ];
    }
}
