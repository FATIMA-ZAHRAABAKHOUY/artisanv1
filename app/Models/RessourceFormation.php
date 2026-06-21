<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RessourceFormation extends Model
{
    use HasFactory;
 
    protected $table = 'ressources_formation';
 
    protected $fillable = [
        'formation_id',
        'type',
        'titre',
        'description',
        'url',
        'duree_secondes',
        'resolution',
        'auteur',
        'nb_pages',
        'taille_ko',
        'est_public',
        'ordre',
    ];
 
    protected $casts = [
        'est_public' => 'boolean',
    ];
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
 
    public function getUrlCompleteAttribute(): string
    {
        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        if (str_starts_with($this->url, '/storage/')) {
            return asset(ltrim($this->url, '/'));
        }

        if (str_starts_with($this->url, '/')) {
            return asset(ltrim($this->url, '/'));
        }

        return asset('storage/' . $this->url);
    }

    public function isUploadedFile(): bool
    {
        return ! str_starts_with($this->url, 'http://')
            && ! str_starts_with($this->url, 'https://');
    }
}
