<?php

namespace App\Repositories;

use App\Models\Artisan;
use App\Models\Avis;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Formateur;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\LigneCommande;
use App\Models\Livraison;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\Produit;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Requêtes agrégées pour le tableau de bord admin.
 * Compatible PostgreSQL / MySQL — statuts EN + FR selon la base réelle.
 */
class DashboardRepository
{
    /** Statuts PostgreSQL réels (enum EN). */
    private const CMD_DELIVERED = ['delivered'];
    private const CMD_CANCELLED = ['cancelled'];
    private const LIV_DELIVERED   = ['delivered'];
    private const LIV_TRANSIT     = ['in_transit'];
    private const LIV_FAILED      = ['failed'];

    public function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /** Compte sécurisé — ne lève pas d'exception si la table est absente. */
    private function safeCount(string $table): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    // ─── Compteurs principaux ────────────────────────────────────────────────

    public function stats(): array
    {
        $users = User::query();
        $artisans = Artisan::query();

        return [
            'users_total'           => (clone $users)->count(),
            'users_clients'         => (clone $users)->where('role', 'client')->count(),
            'users_artisans'        => (clone $users)->where('role', 'artisan')->count(),
            'users_apprenants'      => (clone $users)->where('role', 'apprenant')->count(),
            'users_livreurs'        => (clone $users)->where('role', 'livreur')->count(),
            'users_admins'          => (clone $users)->where('role', 'admin')->count(),
            'artisans_actifs'       => (clone $artisans)->where('statut', 'actif')->where('is_verified', true)->count(),
            'artisans_en_attente'   => (clone $artisans)->where('is_verified', false)->count(),
            'formateurs'            => $this->hasTable('formateurs') ? Formateur::count() : 0,
            'produits_total'        => Produit::count(),
            'produits_actifs'       => Produit::where('is_active', true)->count(),
            'produits_rupture'      => Produit::where('is_active', true)->where('stock', 0)->count(),
            'categories'            => Categorie::count(),
            'avis_total'            => Avis::count(),
            'avis_moyenne'          => round((float) Avis::avg('note'), 2),
            'commandes_total'       => Commande::count(),
            'commandes_pending'     => Commande::where('statut', 'pending')->count(),
            'commandes_confirmed'   => Commande::where('statut', 'confirmed')->count(),
            'commandes_delivered'   => Commande::whereIn('statut', self::CMD_DELIVERED)->count(),
            'commandes_cancelled'   => Commande::whereIn('statut', self::CMD_CANCELLED)->count(),
            'ca_total'              => (float) Paiement::where('statut', 'paid')->sum('montant'),
            'paiements_paid'        => Paiement::where('statut', 'paid')->count(),
            'paiements_pending'     => Paiement::where('statut', 'pending')->count(),
            'livraisons_transit'    => Livraison::whereIn('statut', self::LIV_TRANSIT)->count(),
            'livraisons_done'       => Livraison::whereIn('statut', self::LIV_DELIVERED)->count(),
            'livraisons_sans_livreur' => Livraison::sansLivreurActives()->count(),
            'formations_actives'    => Formation::where('is_active', true)->count(),
            'formations_terminees'  => Formation::where('date_fin', '<', now()->toDateString())->count(),
            'inscriptions_total'    => InscriptionFormation::count(),
            'inscriptions_actives'  => InscriptionFormation::where(function ($q) {
                $q->whereIn('statut', ['inscrit', 'confirme'])
                    ->orWhere('statut_inscription', 'en_cours');
            })->count(),
            'progression_moyenne'   => $this->avgProgression(),
            'fournisseurs'          => $this->safeCount('fournisseurs'),
            'suggestions_achat'     => $this->safeCount('suggestions_achat'),
            'notifications_non_lues'=> Notification::where('is_read', false)->count(),
            'support_ouverts'       => Support::whereIn('statut', ['ouvert', 'en_cours'])->count(),
            'stock_total'           => (int) Produit::sum('stock'),
        ];
    }

    // ─── KPI avancés ─────────────────────────────────────────────────────────

    public function kpis(): array
    {
        $totalCommandes   = max(Commande::count(), 1);
        $delivered        = Commande::whereIn('statut', self::CMD_DELIVERED)->count();
        $paidCount        = Paiement::where('statut', 'paid')->count();
        $paiementTotal    = max(Paiement::count(), 1);
        $livraisonTotal   = max(Livraison::count(), 1);
        $livraisonOk      = Livraison::whereIn('statut', self::LIV_DELIVERED)->count();

        $panierMoyen = (float) Paiement::where('statut', 'paid')->avg('montant');

        $topProduit = DB::table('lignes_commande')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->whereIn('commandes.statut', self::CMD_DELIVERED)
            ->selectRaw('produits.nom, SUM(lignes_commande.quantite) as qte')
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('qte')
            ->first();

        $topArtisan = DB::table('lignes_commande')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('artisans', 'artisans.id', '=', 'produits.artisan_id')
            ->join('users', 'users.id', '=', 'artisans.user_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->whereIn('commandes.statut', self::CMD_DELIVERED)
            ->selectRaw("CONCAT(users.nom, ' ', users.prenom) as nom, SUM(lignes_commande.sous_total) as ca")
            ->groupBy('artisans.id', 'users.nom', 'users.prenom')
            ->orderByDesc('ca')
            ->first();

        $topFormation = Formation::withCount([
            'inscriptions as inscrits' => fn ($q) => $q->whereIn('statut', ['inscrit', 'confirme']),
        ])->orderByDesc('inscrits')->first();

        $topFournisseur = null;
        if ($this->hasTable('fournisseurs') && $this->hasTable('fournisseur_materiaux')) {
            try {
                $topFournisseur = DB::table('fournisseur_materiaux')
                ->join('fournisseurs', 'fournisseurs.id', '=', 'fournisseur_materiaux.fournisseur_id')
                ->where('fournisseur_materiaux.est_recommande', true)
                ->selectRaw('fournisseurs.nom, COUNT(*) as nb')
                ->groupBy('fournisseurs.id', 'fournisseurs.nom')
                ->orderByDesc('nb')
                ->first();
            } catch (\Throwable) {
                $topFournisseur = null;
            }
        }

        return [
            'taux_conversion'      => round(($delivered / $totalCommandes) * 100, 1),
            'panier_moyen'         => round($panierMoyen, 2),
            'produit_top'          => $topProduit?->nom ?? '—',
            'artisan_top'          => $topArtisan?->nom ?? '—',
            'formation_top'        => $topFormation?->titre ?? '—',
            'fournisseur_top'      => $topFournisseur?->nom ?? '—',
            'taux_satisfaction'    => round((float) Avis::avg('note') / 5 * 100, 1),
            'taux_paiement_ok'     => round(($paidCount / $paiementTotal) * 100, 1),
            'taux_livraison_ok'    => round(($livraisonOk / $livraisonTotal) * 100, 1),
            'stock_total'          => (int) Produit::sum('stock'),
        ];
    }

    // ─── Données graphiques ────────────────────────────────────────────────────

    public function charts(): array
    {
        $isPg = DB::getDriverName() === 'pgsql';

        $fmtMonth = $isPg
            ? fn (string $col) => "TO_CHAR(DATE_TRUNC('month', {$col}), 'YYYY-MM')"
            : fn (string $col) => "DATE_FORMAT({$col}, '%Y-%m')";

        $grpMonth = $isPg
            ? fn (string $col) => "DATE_TRUNC('month', {$col})"
            : fn (string $col) => "DATE_FORMAT({$col}, '%Y-%m')";

        $commandesParMois = DB::table('commandes')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("{$fmtMonth('created_at')} as mois, COUNT(*) as total")
            ->groupBy(DB::raw($grpMonth('created_at')))
            ->orderBy('mois')
            ->get();

        $revenusParMois = DB::table('paiements')
            ->where('statut', 'paid')
            ->where('paid_at', '>=', now()->subMonths(12))
            ->selectRaw("{$fmtMonth('paid_at')} as mois, SUM(montant) as total")
            ->groupBy(DB::raw($grpMonth('paid_at')))
            ->orderBy('mois')
            ->get();

        $produitsParCategorie = DB::table('produits')
            ->join('categories', 'categories.id', '=', 'produits.categorie_id')
            ->selectRaw('categories.nom as label, COUNT(produits.id) as total')
            ->groupBy('categories.id', 'categories.nom')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $usersParRole = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->get();

        $commandesParStatut = Commande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        $livraisonsParStatut = Livraison::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        $paiementsParStatut = Paiement::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        $progressionFormations = DB::table('formations')
            ->join('inscriptions_formations', 'inscriptions_formations.formation_id', '=', 'formations.id')
            ->where('formations.is_active', true)
            ->whereNotNull('inscriptions_formations.progression')
            ->selectRaw('formations.titre, AVG(CAST(inscriptions_formations.progression AS NUMERIC)) as progression_avg')
            ->groupBy('formations.id', 'formations.titre')
            ->orderByDesc('progression_avg')
            ->limit(8)
            ->get();

        $topArtisans = DB::table('lignes_commande')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('artisans', 'artisans.id', '=', 'produits.artisan_id')
            ->join('users', 'users.id', '=', 'artisans.user_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->whereIn('commandes.statut', self::CMD_DELIVERED)
            ->selectRaw("CONCAT(users.nom, ' ', users.prenom) as nom, SUM(lignes_commande.sous_total) as ca")
            ->groupBy('artisans.id', 'users.nom', 'users.prenom')
            ->orderByDesc('ca')
            ->limit(10)
            ->get();

        $topProduits = DB::table('lignes_commande')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->whereIn('commandes.statut', self::CMD_DELIVERED)
            ->selectRaw('produits.nom as nom, SUM(lignes_commande.quantite) as qte')
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('qte')
            ->limit(10)
            ->get();

        $topFournisseurs = collect();
        if ($this->hasTable('fournisseurs') && $this->hasTable('fournisseur_materiaux')) {
            try {
                $topFournisseurs = DB::table('fournisseur_materiaux')
                ->join('fournisseurs', 'fournisseurs.id', '=', 'fournisseur_materiaux.fournisseur_id')
                ->where('fournisseur_materiaux.est_recommande', true)
                ->selectRaw('fournisseurs.nom, COUNT(*) as nb')
                ->groupBy('fournisseurs.id', 'fournisseurs.nom')
                ->orderByDesc('nb')
                ->limit(10)
                ->get();
            } catch (\Throwable) {
                $topFournisseurs = collect();
            }
        }

        $inscriptionsParFormation = Formation::withCount([
            'inscriptions as inscrits' => fn ($q) => $q->whereIn('statut', ['inscrit', 'confirme']),
        ])->orderByDesc('inscrits')->limit(10)->get(['id', 'titre']);

        return compact(
            'commandesParMois',
            'revenusParMois',
            'produitsParCategorie',
            'usersParRole',
            'commandesParStatut',
            'livraisonsParStatut',
            'paiementsParStatut',
            'progressionFormations',
            'topArtisans',
            'topProduits',
            'topFournisseurs',
            'inscriptionsParFormation',
        );
    }

    // ─── Widgets (listes récentes) ───────────────────────────────────────────

    public function widgets(): array
    {
        return [
            'commandes'      => Commande::with('client')->latest()->limit(8)->get(),
            'users'          => User::latest()->limit(8)->get(),
            'produits'       => Produit::with(['artisan.user', 'categorie'])->latest()->limit(8)->get(),
            'formations'     => Formation::with('artisan.user')->latest()->limit(8)->get(),
            'notifications'  => Notification::with('user')->latest()->limit(8)->get(),
            'support'        => Support::with('user')->latest()->limit(8)->get(),
            'livraisons'     => Livraison::with(['commande.client', 'livreur'])->latest()->limit(8)->get(),
            'paiements'      => Paiement::with('commande.client')->latest()->limit(8)->get(),
        ];
    }

    /** Timeline d'activité récente (agrégation multi-entités). */
    public function timeline(): Collection
    {
        $items = collect();

        Commande::latest()->limit(5)->get()->each(fn ($c) => $items->push([
            'type'  => 'commande',
            'icon'  => 'fa-bag-shopping',
            'color' => '#c06b5a',
            'text'  => "Commande #{$c->id} — {$c->statut}",
            'at'    => $c->created_at,
        ]));

        User::latest()->limit(4)->get()->each(fn ($u) => $items->push([
            'type'  => 'user',
            'icon'  => 'fa-user-plus',
            'color' => '#6b8cba',
            'text'  => "Inscription : {$u->nom_complet} ({$u->role})",
            'at'    => $u->created_at,
        ]));

        Produit::latest()->limit(4)->get()->each(fn ($p) => $items->push([
            'type'  => 'produit',
            'icon'  => 'fa-shirt',
            'color' => '#4db88c',
            'text'  => "Produit ajouté : {$p->nom}",
            'at'    => $p->created_at,
        ]));

        Support::where('statut', 'ouvert')->latest()->limit(3)->get()->each(fn ($s) => $items->push([
            'type'  => 'support',
            'icon'  => 'fa-headset',
            'color' => '#7a8ba8',
            'text'  => "Ticket : {$s->objet}",
            'at'    => $s->created_at,
        ]));

        return $items->sortByDesc('at')->take(15)->values();
    }

    /** Moyenne progression (colonne varchar castée en NUMERIC pour PostgreSQL). */
    private function avgProgression(?int $formationId = null): float
    {
        $q = DB::table('inscriptions_formations')
            ->whereNotNull('progression');

        if ($formationId) {
            $q->where('formation_id', $formationId);
        }

        $avg = $q->selectRaw('AVG(CAST(progression AS NUMERIC)) as avg')->value('avg');

        return round((float) $avg, 1);
    }
}
