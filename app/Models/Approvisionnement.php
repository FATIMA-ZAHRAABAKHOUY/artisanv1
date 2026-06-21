<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approvisionnement extends Model
{
    use HasFactory;
 
    protected $table = 'approvisionnements';
 
    public $timestamps = false;
 
    protected $fillable = [
        'artisan_id',
        'fournisseur_id',
        'est_principal',
        'notes',
    ];
 
    protected $casts = [
        'est_principal' => 'boolean',
        'created_at'    => 'datetime',
    ];
 
    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }
 
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }
}
