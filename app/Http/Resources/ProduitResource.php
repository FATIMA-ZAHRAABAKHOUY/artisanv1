<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{public function toArray(Request $request): array
    {
        // Calculer la note moyenne si les avis sont chargés
        $noteMoyenne = $this->relationLoaded('avis')
            ? round($this->avis->avg('note') ?? 0, 2)
            : $this->note_moyenne ?? 0;
 
        return [
            'id'           => $this->id,
            'nom'          => $this->nom,
            'description'  => $this->description,
            'prix'         => (float) $this->prix,
            'stock'        => $this->stock,
            'en_stock'     => $this->stock > 0,
            'images'       => collect($this->images ?? [])
                                ->map(fn($img) => asset("storage/{$img}"))
                                ->values(),
            'image_principale' => isset($this->images[0])
                                    ? asset("storage/{$this->images[0]}")
                                    : null,
            'poids'        => $this->poids,
            'dimensions'   => $this->dimensions,
            'slug'         => $this->slug,
            'is_active'    => $this->is_active,
            'note_moyenne' => $noteMoyenne,
            'nb_avis'      => $this->relationLoaded('avis')
                                ? $this->avis->count()
                                : null,
 
            // Catégorie
            'categorie'    => $this->whenLoaded('categorie',
                                fn() => $this->categorie
                                        ? new CategorieResource($this->categorie)
                                        : null
                             ),
 
            // Artisan
            'artisan'      => $this->whenLoaded('artisan', fn() => [
                'id'           => $this->artisan->id,
                'nom'          => $this->artisan->user->nom_complet,
                'specialite'   => $this->artisan->specialite,
                'note_moyenne' => $this->artisan->note_moyenne,
                'is_verified'  => $this->artisan->is_verified,
                'avatar_url'   => $this->artisan->user->avatar_url,
            ]),
 
            // Avis — seulement en mode détail
            'avis'         => $this->whenLoaded('avis',
                                fn() => AvisResource::collection($this->avis)
                             ),
 
            'created_at'   => $this->created_at?->format('d/m/Y'),
            'updated_at'   => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
