<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InscriptionFormation extends Model
{
    use HasFactory;
 
    protected $table = 'inscriptions_formations';
 // app/Models/InscriptionFormation.php
protected $fillable = [
    'formation_id',
    'apprenant_id',
    'statut',
    'date_inscription',
    'statut_inscription',
    'progression',      // ← doit être présen
    'date_debut_reelle',
];
 
    protected $casts = [
        'date_inscription'  => 'datetime',
        'date_debut_reelle' => 'date',
        'date_fin_reelle'   => 'date',
        'note_finale'       => 'decimal:2',
        'progression'       => 'integer',
    ];
 
    // ── Relations ────────────────────────────────────────────────
 
    public function formation()
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
 
    public function apprenant()
    {
        return $this->belongsTo(User::class, 'apprenant_id');
    }
 
    // ── Scopes ───────────────────────────────────────────────────
 
    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['inscrit', 'confirme']);
    }

    public function scopeTerminees($query)
    {
        return $query->where('statut_inscription', 'terminee');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function estEnCours(): bool
    {
        return in_array($this->statut, ['inscrit', 'confirme'], true);
    }

    public function estTerminee(): bool
    {
        return $this->statut_inscription === 'terminee';
    }
 
    // Mettre à jour la progression (OCL : 100% → terminée auto)
    public function mettreAJourProgression(int $progression): void
    {
        $this->progression = min(100, max(0, $progression));
 
        if ($this->progression === 100) {
            $this->statut_inscription = 'terminee';
            $this->date_fin_reelle    = now()->toDateString();
        }
 
        $this->save();
    }
 
    // Abandonner la formation
    public function abandonner(): void
    {
        $this->update([
            'statut_inscription' => 'abandonnee',
            'date_fin_reelle'    => now()->toDateString(),
        ]);
    }
}
