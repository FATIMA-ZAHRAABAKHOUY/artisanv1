<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriauFormation extends Model
{
    use HasFactory;
 
    protected $table = 'materiaux_formation';
 
    protected $fillable = [
        'formation_id',
        'nom',
        'type',
        'couleur',
        'quantite',
        'unite',
        'description',
        'est_fourni',
        'image',
        'ordre',
    ];
 
    protected $casts = [
        'est_fourni' => 'boolean',
        'quantite'   => 'decimal:2',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
 
    // Matériau → FournisseurMateriaux (1-N)
    // Les fournisseurs qui vendent ce matériau
    public function fournisseurs()
    {
        return $this->hasMany(FournisseurMateriau::class, 'materiau_id');
    }
 
    // Fournisseur recommandé
    public function fournisseurRecommande()
    {
        return $this->hasOne(FournisseurMateriau::class, 'materiau_id')
                    ->where('est_recommande', true);
    }
}
