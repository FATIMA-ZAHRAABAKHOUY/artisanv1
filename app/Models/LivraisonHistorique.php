<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LivraisonHistorique extends Model
{
     use HasFactory;
 
    protected $table = 'livraison_historiques';
 
    // Pas de updated_at
    const UPDATED_AT = null;
 
    protected $fillable = [
        'livraison_id',
        'statut',
        'commentaire',
        'localisation',
        'changed_by',
    ];
 
    protected $casts = [
        'created_at' => 'datetime',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function livraison()
    {
        return $this->belongsTo(Livraison::class, 'livraison_id');
    }
 
    public function modifiePar()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
