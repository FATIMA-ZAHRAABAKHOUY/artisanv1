<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;
 
    protected $table = 'categories';
 
    protected $fillable = [
        'nom',
        'description',
        'image',
        'parent_id',
        'slug',
    ];
 
    // ════════════════════════════════════════════════════════════
    // RELATIONS CORRIGÉES
    // ════════════════════════════════════════════════════════════
 
    // Catégorie parente (auto-référence)
    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }
 
    /**
     * Sous-catégories (auto-référence)
     * Changé 'enfants' par 'sousCategories' pour correspondre aux appels de votre projet
     */
    public function sousCategories()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    /**
     * Optionnel : On garde un alias vers 'enfants' au cas où vous l'utiliseriez ailleurs
     */
    public function enfants()
    {
        return $this->sousCategories();
    }
 
    // Produits de cette catégorie
    public function produits()
    {
        return $this->hasMany(Produit::class, 'categorie_id');
    }

    // ════════════════════════════════════════════════════════════
    // SCOPES & HELPERS UTILS
    // ════════════════════════════════════════════════════════════

    /**
     * Image affichée pour la catégorie (fichier public ou colonne image).
     */
    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image)) {
            if (str_starts_with($this->image, 'http') || str_starts_with($this->image, '/')) {
                return $this->image;
            }

            return asset('storage/'.$this->image);
        }

        $slug = $this->slug ?: \Illuminate\Support\Str::slug($this->nom);
        $path = public_path("images/categories/{$slug}.jpg");

        if (is_file($path)) {
            return asset("images/categories/{$slug}.jpg");
        }

        return asset('images/categories/default.jpg');
    }

    /**
     * Permet de récupérer uniquement les catégories principales (sans parent)
     */
    public function scopePrincipales($query)
    {
        return $query->whereNull('parent_id');
    }
}