<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
class Commande extends Model
{
    use HasFactory;
 
    protected $table = 'commandes';
 
    protected $fillable = [
        'client_id',
        'statut',
        'adresse_livraison',
        'ville',
        'code_postal',
        'total_ht',
        'tva',
        'total_ttc',
        'notes',
    ];
 
    protected $casts = [
        'total_ht'  => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'tva'       => 'decimal:2',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    // Commande → Client (N-1)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
 
    // Commande → LignesCommande (1-N)
    public function lignes()
    {
        return $this->hasMany(LigneCommande::class, 'commande_id');
    }
 
    // Commande → Paiement (1-1)
    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'commande_id');
    }
 
    // Commande → Livraison (1-1)
    public function livraison()
    {
        return $this->hasOne(Livraison::class, 'commande_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'pending');
    }
 
    public function scopeConfirmees($query)
    {
        return $query->where('statut', 'confirmed');
    }
 
    public function scopeLivrees($query)
    {
        return $query->where('statut', 'delivered');
    }
 
    public function scopeAnnulees($query)
    {
        return $query->where('statut', 'cancelled');
    }
 
    public function scopeParStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }
 
    public function scopeParClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    public function isPending(): bool    { return $this->statut === 'pending'; }
    public function isCancelled(): bool  { return $this->statut === 'cancelled'; }
    public function isDelivered(): bool  { return $this->statut === 'delivered'; }
 
    public function peutEtreAnnulee(): bool
    {
        return in_array($this->statut, ['pending', 'confirmed']);
    }
 
    // Recalcule le total depuis les lignes
    public function recalculerTotal(): void
    {
        $totalHt  = $this->lignes()->sum('sous_total');
        $totalTtc = round($totalHt * (1 + $this->tva), 2);
 
        $this->update([
            'total_ht'  => $totalHt,
            'total_ttc' => $totalTtc,
        ]);
    }
}