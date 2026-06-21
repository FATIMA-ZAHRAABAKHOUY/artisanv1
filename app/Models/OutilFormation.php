<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutilFormation extends Model
{
    use HasFactory;
 
    protected $table = 'outils_formation';
 
    protected $fillable = [
        'formation_id',
        'nom',
        'description',
        'quantite',
        'est_fourni',
        'image',
        'lien_achat',
        'ordre',
    ];
 
    protected $casts = [
        'est_fourni' => 'boolean',
        'quantite'   => 'integer',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
 
    // Outil → FournisseurOutils (1-N)
    public function fournisseurs()
    {
        return $this->hasMany(FournisseurOutil::class, 'outil_id');
    }
 
    public function fournisseurRecommande()
    {
        return $this->hasOne(FournisseurOutil::class, 'outil_id')
                    ->where('est_recommande', true);
    }
}
