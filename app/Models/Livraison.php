<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Livraison extends Model
{
    use HasFactory;

    /** Valeurs réelles de l'enum PostgreSQL `statut_livraison`. */
    public const STATUT_ASSIGNEE   = 'assigned';
    public const STATUT_EN_TRANSIT = 'in_transit';
    public const STATUT_LIVREE     = 'delivered';
    public const STATUT_ECHOUEE    = 'failed';

    /** Alias sémantiques (compatibilité livreur / vues). */
    public const STATUT_PREPAREE   = self::STATUT_ASSIGNEE;
    public const STATUT_EXPEDIEE   = self::STATUT_EN_TRANSIT;
    public const STATUT_RETOURNEE  = self::STATUT_ECHOUEE;

    public const STATUTS_TERMINAUX = [
        self::STATUT_LIVREE,
        self::STATUT_ECHOUEE,
    ];

    public const STATUTS_ACTIFS = [
        self::STATUT_ASSIGNEE,
        self::STATUT_EN_TRANSIT,
    ];

    protected $table = 'livraisons';

    protected $fillable = [
        'commande_id',
        'livreur_id',
        'statut',
        'adresse',
        'date_livraison_prev',
        'date_livree',
        'notes',
    ];

    protected $casts = [
        'livreur_id'          => 'integer',
        'date_livraison_prev' => 'date',
        'date_livree'         => 'datetime',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    public function livreur()
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function historique()
    {
        return $this->hasMany(LivraisonHistorique::class, 'livraison_id')
            ->orderByDesc('created_at');
    }

    public function historiques()
    {
        return $this->historique();
    }

    public function estLivree(): bool
    {
        return $this->statut === self::STATUT_LIVREE;
    }

    public function peutChangerStatut(): bool
    {
        return ! in_array($this->statut, self::STATUTS_TERMINAUX, true);
    }

    public function scopeSansLivreurActives($query)
    {
        return $query->whereNull('livreur_id')
            ->whereNotIn('statut', self::STATUTS_TERMINAUX);
    }

    public function scopePourLivreur($query, int $livreurId)
    {
        return $query->where('livreur_id', $livreurId);
    }

    public static function statutLabel(?string $statut): string
    {
        return match ($statut) {
            self::STATUT_ASSIGNEE   => 'À préparer',
            self::STATUT_EN_TRANSIT => 'En route',
            self::STATUT_LIVREE     => 'Livrée',
            self::STATUT_ECHOUEE    => 'Retournée',
            default                 => $statut ?? '—',
        };
    }

    /** Crée une livraison pour une commande (idempotent — une seule par commande). */
    public static function creerPourCommande(Commande $commande): self
    {
        $adresse = trim(implode(', ', array_filter([
            $commande->adresse_livraison,
            $commande->ville,
            $commande->code_postal,
        ])));

        return self::firstOrCreate(
            ['commande_id' => $commande->id],
            [
                'livreur_id'          => null,
                'adresse'             => $adresse ?: null,
                'statut'              => self::STATUT_ASSIGNEE,
                'date_livraison_prev' => now()->addDays(3)->toDateString(),
                'notes'               => $commande->notes,
            ]
        );
    }

    public function changerStatut(string $statut, int $userId, ?string $commentaire = null): void
    {
        if ($this->estLivree()) {
            throw new \RuntimeException('Livraison déjà confirmée comme livrée, modification impossible.');
        }

        $payload = ['statut' => $statut];
        if ($commentaire !== null) {
            $payload['notes'] = $commentaire;
        }
        $this->update($payload);

        $historique = [
            'statut'      => $statut,
            'commentaire' => $commentaire,
        ];
        if (Schema::hasColumn('livraison_historiques', 'changed_by')) {
            $historique['changed_by'] = $userId;
        }

        $this->historique()->create($historique);
    }

    public function getAdresseLivraisonAttribute(): ?string
    {
        return $this->attributes['adresse']
            ?? $this->attributes['adresse_livraison']
            ?? $this->commande?->adresse_livraison;
    }

    public function getDateLivraisonPrevueAttribute()
    {
        return $this->date_livraison_prev ?? null;
    }

    public function getDateLivraisonReelleAttribute()
    {
        return $this->date_livree;
    }

    public function getVilleAttribute(): ?string
    {
        return $this->attributes['ville'] ?? $this->commande?->ville;
    }

    public function getNumeroSuiviAttribute(): ?string
    {
        return $this->attributes['numero_suivi'] ?? null;
    }

    public function getTransporteurAttribute(): ?string
    {
        return $this->attributes['transporteur'] ?? null;
    }

    public function getPreuveLivraisonUrlAttribute(): ?string
    {
        $path = $this->attributes['preuve_livraison_url'] ?? null;

        return $path ? asset('storage/'.$path) : null;
    }
}
