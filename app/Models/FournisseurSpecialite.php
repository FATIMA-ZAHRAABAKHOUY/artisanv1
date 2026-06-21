<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FournisseurSpecialite extends Model
{
    use HasFactory;
 
    protected $table = 'fournisseur_specialites';
 
    public $timestamps = false;
 
    protected $fillable = ['fournisseur_id', 'specialite'];
 
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }
}
