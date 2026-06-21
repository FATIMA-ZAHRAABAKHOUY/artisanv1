<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fournisseur extends Model
{
     use HasFactory;
 
    protected $table = 'fournisseurs';
 
    protected $fillable = [
        'nom',
        'type',
        'statut',
        'email',
        'telephone',
        'whatsapp',
        'adresse',
        'ville',
        'region',
        'code_postal',
        'site_web',
        'logo',
        'description',
        'remise_cooperative',
        'delai_livraison_min',
        'delai_livraison_max',
        'note_moyenne',
        'user_id',
    ];
 
    protected $casts = [
        'remise_cooperative' => 'decimal:2',
        'note_moyenne'       => 'decimal:2',
    ];
 
    // ── Relations ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    // Fournisseur → Spécialités (1-N)
    public function specialites()
    {
        return $this->hasMany(FournisseurSpecialite::class, 'fournisseur_id');
    }
 
    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'fournisseur_id');
    }

    // Fournisseur ↔ Artisans (N-N via approvisionnements)
    public function artisans()
    {
        return $this->belongsToMany(
            Artisan::class,
            'approvisionnements',
            'fournisseur_id',
            'artisan_id'
        )->withPivot('est_principal', 'notes');
    }
 
    // Fournisseur → FournisseurMateriaux (1-N)
    public function materiaux()
    {
        return $this->hasMany(FournisseurMateriau::class, 'fournisseur_id');
    }
 
    // Fournisseur → FournisseurOutils (1-N)
    public function outils()
    {
        return $this->hasMany(FournisseurOutil::class, 'fournisseur_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }
 
    public function scopeLocaux($query)
    {
        return $query->where('type', 'local');
    }
 
    public function scopeNationaux($query)
    {
        return $query->where('type', 'national');
    }
 
    public function scopeEnLigne($query)
    {
        return $query->where('type', 'en_ligne');
    }
 
    public function scopeParVille($query, string $ville)
    {
        return $query->where('ville', 'ilike', "%{$ville}%");
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    public function isLocal(): bool    { return $this->type === 'local'; }
    public function isNational(): bool { return $this->type === 'national'; }
    public function isEnLigne(): bool  { return $this->type === 'en_ligne'; }
 
    public function getLogoUrlAttribute(): ?string
    {
        return $this->getLogoUrl();
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'local'    => '🏪 Local',
            'national' => '🚚 National',
            'en_ligne' => '🌐 En ligne',
            default    => $this->type,
        };
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
