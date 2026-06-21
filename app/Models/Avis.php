<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;
 
    protected $table = 'avis';
 
    // Pas de updated_at dans la table
    const UPDATED_AT = null;
 
    protected $fillable = [
        'produit_id',
        'client_id',
        'note',
        'commentaire',
    ];
 
    protected $casts = [
        'note'       => 'integer',
        'created_at' => 'datetime',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
 
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
 