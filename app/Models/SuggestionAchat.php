<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuggestionAchat extends Model
{
     use HasFactory;
 
    protected $table = 'suggestion_achat';
 
    public $timestamps = false;
 
    protected $fillable = [
        'apprenant_id',
        'formation_id',
        'fournisseur_id',
        'type_objet',
        'objet_id',
        'est_clique',
        'est_achete',
        'created_at',
    ];
 
    protected $casts = [
        'est_clique'  => 'boolean',
        'est_achete'  => 'boolean',
        'created_at'  => 'datetime',
    ];
 
    public function apprenant()   { return $this->belongsTo(User::class, 'apprenant_id'); }
    public function formation()   { return $this->belongsTo(Formation::class, 'formation_id'); }
    public function fournisseur() { return $this->belongsTo(Fournisseur::class, 'fournisseur_id'); }
}
