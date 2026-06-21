<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens, HasFactory, Notifiable;

    // ── Table ────────────────────────────────────────────────────
    protected $table = 'users';

    // ── Champs autorisés ─────────────────────────────────────────
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
        'role',
        'statut',
        'avatar',
    ];

    // ── Champs cachés (jamais dans JSON) ─────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts ────────────────────────────────────────────────────
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ════════════════════════════════════════════════════════════
    // RELATIONS
    // ════════════════════════════════════════════════════════════

    // Un user peut être un artisan (1-1)
    public function artisan()
    {
        return $this->hasOne(Artisan::class, 'user_id');
    }

    public function livreur()
    {
        return $this->hasOne(Livreur::class, 'user_id');
    }

    public function fournisseur()
    {
        return $this->hasOne(Fournisseur::class, 'user_id');
    }

    // Un user peut être un formateur (1-1)
    public function formateur()
    {
        return $this->hasOne(Formateur::class, 'user_id');
    }

    // Un client passe plusieurs commandes (1-N)
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'client_id');
    }

    // Un apprenant s'inscrit à plusieurs formations (1-N)
    public function inscriptions()
    {
        return $this->hasMany(InscriptionFormation::class, 'apprenant_id');
    }

    public function formationApprentis()
    {
        return $this->hasMany(FormationApprenti::class, 'apprenti_id');
    }

    public function formationsApprenti()
    {
        return $this->belongsToMany(Formation::class, 'formation_apprentis', 'apprenti_id', 'formation_id')
            ->withPivot(['statut', 'progression', 'date_inscription', 'date_completion', 'certificat_url'])
            ->withTimestamps();
    }

    // Un livreur a plusieurs livraisons assignées (1-N)
    public function livraisons()
    {
        return $this->hasMany(Livraison::class, 'livreur_id');
    }

    // Un user reçoit plusieurs notifications (1-N)
    public function notifications_custom()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // Un client laisse plusieurs avis (1-N)
    public function avis()
    {
        return $this->hasMany(Avis::class, 'client_id');
    }

    // Un user ouvre des tickets support (1-N)
    public function tickets()
    {
        return $this->hasMany(Support::class, 'user_id');
    }

    // ════════════════════════════════════════════════════════════
    // SCOPES
    // ════════════════════════════════════════════════════════════

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeParRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeArtisans($query)
    {
        return $query->where('role', 'artisan');
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS (Vérification des rôles)
    // ════════════════════════════════════════════════════════════

    public function isAdmin(): bool     { return $this->role === 'admin'; }
    public function isArtisan(): bool   { return $this->role === 'artisan'; }
    public function isClient(): bool    { return $this->role === 'client'; }
    public function isLivreur(): bool   { return $this->role === 'livreur'; }
    public function isApprenant(): bool { return $this->role === 'apprenant'; }
    public function isFournisseur(): bool { return $this->role === 'fournisseur'; }
    public function isFormateur(): bool  { return $this->role === 'formateur'; }
    public function isActif(): bool     { return $this->statut === 'actif'; }

    // Accessor : nom complet
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} {$this->prenom}";
    }

    // Accessor : URL avatar
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? asset("storage/{$this->avatar}")
            : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' && $this->statut === 'actif';
    }

    public function getFilamentName(): string
    {
        $name = trim("{$this->nom} {$this->prenom}");

        return $name !== '' ? $name : ($this->email ?? 'Utilisateur');
    }
}