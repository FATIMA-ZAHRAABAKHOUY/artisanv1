<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FournisseurOutil extends Model
{
    use HasFactory;
 
    protected $table = 'fournisseur_outils';
 
    protected $fillable = [
        'outil_id',
        'fournisseur_id',
        'nom_produit_fournisseur',
        'reference_produit',
        'prix_unitaire',
        'unite_prix',
        'url_produit',
        'delai_livraison_min',
        'delai_livraison_max',
        'est_recommande',
        'stock_disponible',
        'notes_apprenant',
    ];
 
    protected $casts = [
        'est_recommande'   => 'boolean',
        'stock_disponible' => 'boolean',
        'prix_unitaire'    => 'decimal:2',
    ];
 
    public function outil()
    {
        return $this->belongsTo(OutilFormation::class, 'outil_id');
    }
 
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function getNomProduitAttribute(): ?string
    {
        return $this->nom_produit_fournisseur;
    }
}
