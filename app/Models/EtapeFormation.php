<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EtapeFormation extends Model
{
    use HasFactory;
 
    protected $table = 'etapes_formation';
 
    protected $fillable = [
        'formation_id',
        'numero_ordre',
        'titre',
        'description',
        'duree_minutes',
        'objectif',
        'materiaux_requis',
    ];
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
}
