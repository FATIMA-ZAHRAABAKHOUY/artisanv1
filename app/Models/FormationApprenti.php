<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class FormationApprenti extends Model
{
    public const STATUT_NON_COMMENCEE = 'non_commencee';
    public const STATUT_EN_COURS      = 'en_cours';
    public const STATUT_TERMINEE      = 'terminee';

    protected $table = 'formation_apprentis';

    protected $fillable = [
        'apprenti_id',
        'formation_id',
        'statut',
        'progression',
        'date_inscription',
        'date_completion',
        'certificat_url',
    ];

    protected $casts = [
        'progression'      => 'integer',
        'date_inscription' => 'datetime',
        'date_completion'  => 'datetime',
    ];

    public function apprenti(): BelongsTo
    {
        return $this->belongsTo(User::class, 'apprenti_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function estTerminee(): bool
    {
        return $this->statut === self::STATUT_TERMINEE;
    }

    public function estEnCours(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function aCertificat(): bool
    {
        return $this->estTerminee() && filled($this->certificat_url);
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_COURS      => 'En cours',
            self::STATUT_TERMINEE      => 'Terminée',
            self::STATUT_NON_COMMENCEE => 'Non commencée',
            default                    => ucfirst(str_replace('_', ' ', $this->statut ?? '')),
        };
    }

    public function statutBadgeClass(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_COURS      => 'badge-confirmed',
            self::STATUT_TERMINEE      => 'badge-delivered',
            self::STATUT_NON_COMMENCEE => 'badge-cancelled',
            default                    => 'badge-pending',
        };
    }

    public function getFormateurNomAttribute(): string
    {
        $formation = $this->relationLoaded('formation') ? $this->formation : $this->formation()->with(['formateurs.user', 'artisan.user'])->first();

        if (! $formation) {
            return '—';
        }

        $formateur = $formation->formateurs->first();

        if ($formateur?->user) {
            return $formateur->user->nom_complet;
        }

        return $formation->artisan?->user?->nom_complet ?? '—';
    }

    public function getDureeHeuresAttribute(): int
    {
        $formation = $this->formation;

        if (! $formation) {
            return 0;
        }

        $minutes = $formation->relationLoaded('etapes')
            ? (int) $formation->etapes->sum('duree_minutes')
            : (int) $formation->etapes()->sum('duree_minutes');

        return max(1, (int) round($minutes / 60));
    }

    /** Synchronise depuis inscriptions_formations si la table legacy existe. */
    public static function syncForUser(int $userId): void
    {
        if (! Schema::hasTable('inscriptions_formations')) {
            return;
        }

        InscriptionFormation::query()
            ->where('apprenant_id', $userId)
            ->each(function (InscriptionFormation $inscription) use ($userId) {
                $statut = match ($inscription->statut_inscription) {
                    'terminee'  => self::STATUT_TERMINEE,
                    'en_cours'  => self::STATUT_EN_COURS,
                    'abandonnee'=> self::STATUT_NON_COMMENCEE,
                    default     => ($inscription->progression ?? 0) > 0
                        ? self::STATUT_EN_COURS
                        : self::STATUT_NON_COMMENCEE,
                };

                static::updateOrCreate(
                    [
                        'apprenti_id'  => $userId,
                        'formation_id' => $inscription->formation_id,
                    ],
                    [
                        'statut'           => $statut,
                        'progression'      => min(100, max(0, (int) ($inscription->progression ?? 0))),
                        'date_inscription' => $inscription->date_inscription ?? $inscription->created_at,
                        'date_completion'  => $statut === self::STATUT_TERMINEE
                            ? ($inscription->date_fin_reelle ?? $inscription->updated_at)
                            : null,
                        'certificat_url'   => $inscription->certificat_url ?? null,
                    ]
                );
            });
    }
}
