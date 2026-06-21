<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artisan extends Model
{
    use HasFactory;

    protected $table = 'artisans';

    protected $fillable = [
        'user_id',
        'specialite',
        'cin',
        'bio',
        'rib',
        'note_moyenne',
        'statut',
        'is_verified',
    ];

    protected $casts = [
        'is_verified'  => 'boolean',
        'note_moyenne' => 'decimal:2',
    ];

    // ════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════

    // Un artisan appartient à un user (1-1)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Un artisan publie plusieurs produits (1-N)
    public function produits()
    {
        return $this->hasMany(Produit::class, 'artisan_id');
    }

    // Formations créées par cet artisan (propriétaire)
    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class, 'artisan_id');
    }

    // Formations auxquelles l'artisan est inscrit en tant qu'apprenant (optionnel)
    public function formationsInscrites(): BelongsToMany
    {
        return $this->belongsToMany(
            Formation::class,
            'inscriptions_formations',
            'apprenant_id',
            'formation_id',
            'user_id',
            'id'
        )->withPivot(['statut', 'statut_inscription', 'progression', 'date_inscription']);
    }

    public function possedeFormation(int $formationId): bool
    {
        return $this->formations()->where('id', $formationId)->exists();
    }

    // Un artisan peut être formateur (1-1)
    public function formateur()
    {
        return $this->hasOne(Formateur::class, 'artisan_id');
    }

    // Un artisan s'approvisionne chez plusieurs fournisseurs (N-N)
    public function fournisseurs()
    {
        return $this->belongsToMany(
            Fournisseur::class,
            'approvisionnements',   // table pivot
            'artisan_id',
            'fournisseur_id'
        )->withPivot('est_principal', 'notes')
         ->withTimestamps();
    }

    // Approvisionnnements directs (si besoin d'accéder au pivot)
    public function approvisionnements()
    {
        return $this->hasMany(Approvisionnement::class, 'artisan_id');
    }

    // ════════════════════════════════════════════════════════════
    // SCOPES
    // ════════════════════════════════════════════════════════════

    public function scopeVerifies($query)
    {
        return $query->where('is_verified', true)
                     ->where('statut', 'actif');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeParSpecialite($query, string $specialite)
    {
        return $query->where('specialite', 'ilike', "%{$specialite}%");
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════

    public function isActif(): bool    { return $this->statut === 'actif'; }
    public function isVerifie(): bool  { return $this->is_verified; }

    public function getNomAttribute(): string
    {
        return $this->user->nom_complet ?? '';
    }

    public function getDateAdhesionAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->created_at;
    }
}