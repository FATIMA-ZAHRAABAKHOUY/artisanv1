<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
 
    protected $table = 'produits';
 
    protected $fillable = [
        'artisan_id',
        'categorie_id',
        'nom',
        'description',
        'prix',
        'stock',
        'images',
        'poids',
        'dimensions',
        'is_active',
        'slug',
    ];
 
    protected $casts = [
        'images'    => 'array',   // JSON → tableau PHP automatiquement
        'is_active' => 'boolean',
        'prix'      => 'decimal:2',
        'poids'     => 'decimal:3',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    // Produit → Artisan (N-1)
    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }
 
    // Produit → Catégorie (N-1)
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
 
    // Produit → Avis (1-N)
    public function avis()
    {
        return $this->hasMany(Avis::class, 'produit_id');
    }
 
    // Produit → LignesCommande (1-N)
    public function lignesCommande()
    {
        return $this->hasMany(LigneCommande::class, 'produit_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }
 
    public function scopeEnStock($query)
    {
        return $query->where('stock', '>', 0);
    }
 
    public function scopeDisponibles($query)
    {
        return $query->where('is_active', true)
                     ->where('stock', '>', 0);
    }
 
    public function scopeParCategorie($query, int $categorieId)
    {
        return $query->where('categorie_id', $categorieId);
    }
 
    public function scopeRecherche($query, string $terme)
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom',         'ilike', "%{$terme}%")
              ->orWhere('description','ilike', "%{$terme}%");
        });
    }
 
    public function scopePrixEntre($query, float $min, float $max)
    {
        return $query->whereBetween('prix', [$min, $max]);
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    public function stockSuffisant(int $quantite): bool
    {
        return $this->stock >= $quantite;
    }
 
    // Accessor : note moyenne calculée depuis les avis
    public function getNoteMoyenneAttribute(): float
    {
        return round($this->avis()->avg('note') ?? 0, 2);
    }
 
    // Accessor : première image
    public function getPremierImageAttribute(): ?string
    {
        $images = $this->images;
        if (empty($images)) return null;
        return asset("storage/{$images[0]}");
    }
 
    // Accessor : toutes les URLs d'images
    public function getImagesUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn($img) => asset("storage/{$img}"))
            ->toArray();
    }
}
 