<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Formateur;

class Formation extends Model
{
    use HasFactory;
 
    protected $table = 'formations';
 
    protected $fillable = [
        'artisan_id',
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'prix',
        'places_max',
        'lieu',
        'image',
        'is_active',
    ];
 
    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'is_active'  => 'boolean',
        'prix'       => 'decimal:2',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    // Formation → Artisan propriétaire (N-1)
    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }

    // Formation ↔ Artisans inscrits (N-N via inscriptions_formations)
    public function artisans()
    {
        return $this->belongsToMany(
            Artisan::class,
            'inscriptions_formations',
            'formation_id',
            'apprenant_id',
            'id',
            'user_id'
        )->withPivot(['statut', 'statut_inscription', 'progression', 'date_inscription']);
    }
 
    // Formation ↔ Formateurs (N-N via formation_formateurs)
    public function formateurs()
{
    return $this->belongsToMany(
        Formateur::class,
        'formation_formateurs',
        'formation_id',
        'formateur_id'
    )->withPivot('role');
}
 
    // Formation → Inscriptions (1-N)
    public function inscriptions()
    {
        return $this->hasMany(InscriptionFormation::class, 'formation_id');
    }

    public function apprentis()
    {
        return $this->belongsToMany(User::class, 'formation_apprentis', 'formation_id', 'apprenti_id')
            ->withPivot(['statut', 'progression', 'date_inscription', 'date_completion', 'certificat_url'])
            ->withTimestamps();
    }
 
    // Inscriptions actives seulement
    public function inscriptionsActives()
    {
        return $this->hasMany(InscriptionFormation::class, 'formation_id')
                    ->whereIn('statut', ['inscrit', 'confirme']);
    }
 
    // Formation → Etapes (1-N, ordonnées)
    public function etapes()
    {
        return $this->hasMany(EtapeFormation::class, 'formation_id')
                    ->orderBy('numero_ordre');
    }
 
    // Formation → Matériaux (1-N, ordonnés)
    public function materiaux()
    {
        return $this->hasMany(MateriauFormation::class, 'formation_id')
                    ->orderBy('ordre');
    }
 
    // Formation → Outils (1-N, ordonnés)
    public function outils()
    {
        return $this->hasMany(OutilFormation::class, 'formation_id')
                    ->orderBy('ordre');
    }
 
    // Formation → Ressources pédagogiques (1-N, ordonnées)
    public function ressources()
    {
        return $this->hasMany(RessourceFormation::class, 'formation_id')
                    ->orderBy('ordre');
    }
 
    // Ressources publiques seulement
    public function ressourcesPubliques()
    {
        return $this->hasMany(RessourceFormation::class, 'formation_id')
                    ->where('est_public', true)
                    ->orderBy('ordre');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }
 
    public function scopeAVenir($query)
    {
        return $query->where('is_active', true)
                     ->where('date_debut', '>=', now()->toDateString());
    }
 
    public function scopeParVille($query, string $ville)
    {
        return $query->where('lieu', 'ilike', "%{$ville}%");
    }
 
    public function scopeGratuites($query)
    {
        return $query->where('prix', 0);
    }
 
    // ── Helpers ──────────────────────────────────────────────────
 
    // Nombre de places disponibles
    public function placesDisponibles(): int
    {
        $prises = $this->inscriptionsActives()->count();
        return max(0, $this->places_max - $prises);
    }
 
    public function estComplete(): bool
    {
        return $this->placesDisponibles() === 0;
    }
 
    public function estActive(): bool
    {
        return $this->is_active;
    }
 
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset("storage/{$this->image}") : null;
    }
}
