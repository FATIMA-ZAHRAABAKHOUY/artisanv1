<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;


class LigneCommande extends Model
{
    use HasFactory;
 
    protected $table = 'lignes_commande';
 
    // Pas de updated_at
    const UPDATED_AT = null;
 
    protected $fillable = [
        'commande_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
        'remise',
        'sous_total',
    ];
 
    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'remise'        => 'decimal:2',
        'sous_total'    => 'decimal:2',
        'quantite'      => 'integer',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    // LigneCommande → Commande (N-1)
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }
 
    // LigneCommande → Produit (N-1)
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    // OCL : sousTotal = quantite * prixUnitaire * (1 - remise)
    public function calculerSousTotal(): float
    {
        return round(
            $this->quantite * $this->prix_unitaire * (1 - $this->remise),
            2
        );
    }
}
 