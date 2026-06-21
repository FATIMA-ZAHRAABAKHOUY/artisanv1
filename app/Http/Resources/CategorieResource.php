<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nom'             => $this->nom,
            'description'     => $this->description,
            'slug'            => $this->slug,
            'image_url'       => $this->image
                                    ? asset("storage/{$this->image}")
                                    : null,
            'nb_produits'     => $this->whenCounted('produits'),
 
            // Hiérarchie
            'parent'          => $this->whenLoaded('parent', fn() =>
                                    $this->parent ? [
                                        'id'  => $this->parent->id,
                                        'nom' => $this->parent->nom,
                                    ] : null
                                 ),
            'sous_categories' => $this->whenLoaded('enfants',
                                    fn() => CategorieResource::collection($this->enfants)
                                 ),
        ];
    }
}
