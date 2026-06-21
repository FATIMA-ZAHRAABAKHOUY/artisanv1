<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Support extends Model
{
    use HasFactory;
 
    protected $table = 'support';
 
    protected $fillable = [
        'user_id',
        'objet',
        'description',
        'statut',
        'colis_id',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    // Livraison liée (ticket sur un colis)
    public function livraison()
    {
        return $this->belongsTo(Livraison::class, 'colis_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeOuverts($query)
    {
        return $query->where('statut', 'ouvert');
    }
 
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }
}
