<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    
    use HasFactory;
 
    protected $table = 'paiements';
 
    protected $fillable = [
        'commande_id',
        'methode',
        'statut',
        'montant',
        'reference',
        'gateway_data',
        'paid_at',
    ];
 
    protected $casts = [
        'gateway_data' => 'array',
        'paid_at'      => 'datetime',
        'montant'      => 'decimal:2',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    // Paiement → Commande (1-1)
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopePaies($query)
    {
        return $query->where('statut', 'paid');
    }
 
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'pending');
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    public function estPaye(): bool      { return $this->statut === 'paid'; }
    public function estEnAttente(): bool { return $this->statut === 'pending'; }
    public function estEchoue(): bool    { return $this->statut === 'failed'; }
}
