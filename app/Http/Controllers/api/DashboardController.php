<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Artisan;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Livraison;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/admin/dashboard
    // Statistiques générales
    // ────────────────────────────────────────────────────────────
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [

                // ── Utilisateurs ──────────────────────────────
                'utilisateurs' => [
                    'total_clients'       => User::where('role', 'client')->count(),
                    'total_artisans'      => Artisan::count(),
                    'artisans_verifies'   => Artisan::where('is_verified', true)->count(),
                    'artisans_en_attente' => Artisan::where('is_verified', false)->count(),
                    'total_apprenants'    => User::where('role', 'apprenant')->count(),
                    'total_livreurs'      => User::where('role', 'livreur')->count(),
                ],

                // ── Produits ──────────────────────────────────
                'produits' => [
                    'total_actifs'    => Produit::where('is_active', true)->count(),
                    'en_rupture'      => Produit::where('stock', 0)->where('is_active', true)->count(),
                    'stock_faible'    => Produit::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
                ],

                // ── Commandes ─────────────────────────────────
                'commandes' => [
                    'total'           => Commande::count(),
                    'pending'         => Commande::where('statut', 'pending')->count(),
                    'confirmed'       => Commande::where('statut', 'confirmed')->count(),
                    'processing'      => Commande::where('statut', 'processing')->count(),
                    'shipped'         => Commande::where('statut', 'shipped')->count(),
                    'delivered'       => Commande::where('statut', 'delivered')->count(),
                    'cancelled'       => Commande::where('statut', 'cancelled')->count(),
                    'ce_mois'         => Commande::whereMonth('created_at', now()->month)
                                                 ->whereYear('created_at', now()->year)
                                                 ->count(),
                ],

                // ── Finances ──────────────────────────────────
                'finances' => [
                    'chiffre_affaires_total' => Paiement::where('statut', 'paid')->sum('montant'),
                    'ca_ce_mois'             => Paiement::where('statut', 'paid')
                                                         ->whereMonth('paid_at', now()->month)
                                                         ->whereYear('paid_at', now()->year)
                                                         ->sum('montant'),
                    'ca_mois_dernier'        => Paiement::where('statut', 'paid')
                                                         ->whereMonth('paid_at', now()->subMonth()->month)
                                                         ->whereYear('paid_at', now()->subMonth()->year)
                                                         ->sum('montant'),
                    'paiements_en_attente'   => Paiement::where('statut', 'pending')->count(),
                ],

                // ── Formations ────────────────────────────────
                'formations' => [
                    'total_actives'       => Formation::where('is_active', true)->count(),
                    'total_inscriptions'  => InscriptionFormation::count(),
                    'en_cours'            => InscriptionFormation::where('statut_inscription', 'en_cours')->count(),
                    'terminees'           => InscriptionFormation::where('statut_inscription', 'terminee')->count(),
                ],

                // ── Livraisons ────────────────────────────────
                'livraisons' => [
                    'assignee'     => Livraison::where('statut', Livraison::STATUT_ASSIGNEE)->count(),
                    'en_transit'   => Livraison::where('statut', Livraison::STATUT_EN_TRANSIT)->count(),
                    'livree'       => Livraison::where('statut', Livraison::STATUT_LIVREE)->count(),
                    'echouee'      => Livraison::where('statut', Livraison::STATUT_ECHOUEE)->count(),
                    'sans_livreur' => Livraison::sansLivreurActives()->count(),
                ],
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/dashboard/graphiques
    // Données pour les graphiques
    // ────────────────────────────────────────────────────────────
    public function graphiques(): JsonResponse
    {
        // Détecter automatiquement le driver de base de données pour assurer la compatibilité SQL
        $isPostgres = DB::getDriverName() === 'pgsql';

        $formatMoisPaiement = $isPostgres ? "TO_CHAR(DATE_TRUNC('month', paid_at), 'YYYY-MM')" : "DATE_FORMAT(paid_at, '%Y-%m')";
        $groupByMoisPaiement = $isPostgres ? "DATE_TRUNC('month', paid_at)" : "DATE_FORMAT(paid_at, '%Y-%m')";

        // Ventes par mois
        $ventesParMois = DB::table('paiements')
            ->where('statut', 'paid')
            ->where('paid_at', '>=', now()->subMonths(12))
            ->selectRaw("{$formatMoisPaiement} as mois,
                         SUM(montant) as total,
                         COUNT(*) as nb_paiements")
            ->groupBy(DB::raw($groupByMoisPaiement))
            ->orderBy('mois')
            ->get();

        // Commandes par statut
        $commandesParStatut = DB::table('commandes')
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        // Top 10 produits les plus vendus
        $topProduits = DB::table('lignes_commande')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->where('commandes.statut', 'delivered')
            ->selectRaw('produits.id, produits.nom,
                         SUM(lignes_commande.quantite) as total_vendu,
                         SUM(lignes_commande.sous_total) as chiffre_affaires')
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')
            ->limit(10)
            ->get();

        // Top artisans par revenus
        $topArtisans = DB::table('lignes_commande')
            ->join('produits',  'produits.id',  '=', 'lignes_commande.produit_id')
            ->join('artisans',  'artisans.id',  '=', 'produits.artisan_id')
            ->join('users',     'users.id',     '=', 'artisans.user_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->where('commandes.statut', 'delivered')
            ->selectRaw("artisans.id,
                         CONCAT(users.nom, ' ', users.prenom) as artisan_nom,
                         artisans.specialite,
                         SUM(lignes_commande.sous_total) as revenus,
                         COUNT(DISTINCT commandes.id) as nb_commandes")
            ->groupBy('artisans.id', 'users.nom', 'users.prenom', 'artisans.specialite')
            ->orderByDesc('revenus')
            ->limit(5)
            ->get();

        $formatMoisInscription = $isPostgres ? "TO_CHAR(DATE_TRUNC('month', date_inscription), 'YYYY-MM')" : "DATE_FORMAT(date_inscription, '%Y-%m')";
        $groupByMoisInscription = $isPostgres ? "DATE_TRUNC('month', date_inscription)" : "DATE_FORMAT(date_inscription, '%Y-%m')";

        // Inscriptions formations par mois
        $inscriptionsParMois = DB::table('inscriptions_formations')
            ->where('date_inscription', '>=', now()->subMonths(6))
            ->selectRaw("{$formatMoisInscription} as mois,
                         COUNT(*) as total")
            ->groupBy(DB::raw($groupByMoisInscription))
            ->orderBy('mois')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'ventes_par_mois'        => $ventesParMois,
                'commandes_par_statut'   => $commandesParStatut,
                'top_produits'           => $topProduits,
                'top_artisans'           => $topArtisans,
                'inscriptions_par_mois'  => $inscriptionsParMois,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/stats/ventes
    // ────────────────────────────────────────────────────────────
    public function statsVentes(Request $request): JsonResponse
    {
        $debut = $request->get('debut', now()->startOfMonth()->toDateString());
        $fin   = $request->get('fin',   now()->endOfMonth()->toDateString());

        $stats = DB::table('paiements')
            ->join('commandes', 'commandes.id', '=', 'paiements.commande_id')
            ->where('paiements.statut', 'paid')
            ->whereBetween('paiements.paid_at', [$debut, $fin])
            ->selectRaw('COUNT(*) as nb_ventes,
                         SUM(paiements.montant) as chiffre_affaires,
                         AVG(paiements.montant) as panier_moyen,
                         MIN(paiements.montant) as min_commande,
                         MAX(paiements.montant) as max_commande')
            ->first();

        return response()->json([
            'success' => true,
            'periode' => ['debut' => $debut, 'fin' => $fin],
            'data'    => $stats,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/stats/formations
    // ────────────────────────────────────────────────────────────
    public function statsFormations(): JsonResponse
    {
        // Correction N+1 : eager loading avec structure conditionnelle propre
        $formations = Formation::where('is_active', true)
            ->with(['artisan.user'])
            ->withCount([
                'inscriptions',
                'inscriptions as en_cours_count'  => fn($q) => $q->where('statut_inscription', 'en_cours'),
                'inscriptions as terminees_count' => fn($q) => $q->where('statut_inscription', 'terminee'),
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $formations->map(function($f) {
                $nomArtisan = $f->artisan && $f->artisan->user 
                    ? trim($f->artisan->user->nom . ' ' . $f->artisan->user->prenom) 
                    : 'Artisan Inconnu';

                return [
                    'id'              => $f->id,
                    'titre'           => $f->titre,
                    'artisan'         => $nomArtisan,
                    'places_max'      => $f->places_max,
                    'inscriptions'    => $f->inscriptions_count,
                    'en_cours'        => $f->en_cours_count,
                    'terminees'       => $f->terminees_count,
                    'taux_completion' => $f->inscriptions_count > 0
                        ? round(($f->terminees_count / $f->inscriptions_count) * 100, 1)
                        : 0,
                ];
            }),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/stats/artisans
    // ────────────────────────────────────────────────────────────
    public function statsArtisans(): JsonResponse
    {
        // Correction N+1 : Chargement des relations et comptages liés en une seule fois
        $artisans = Artisan::with('user')
            ->withCount(['produits', 'formations'])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $artisans->map(function($a) {
                $nomComplet = $a->user ? trim($a->user->nom . ' ' . $a->user->prenom) : 'Utilisateur supprimé';
                
                $dateAdhesion = $a->date_adhesion 
                    ? Carbon::parse($a->date_adhesion)->format('d/m/Y') 
                    : null;

                return [
                    'id'             => $a->id,
                    'nom'            => $nomComplet,
                    'specialite'     => $a->specialite,
                    'statut'         => $a->statut,
                    'is_verified'    => (bool) $a->is_verified,
                    'note_moyenne'   => $a->note_moyenne,
                    'nb_produits'    => $a->produits_count,
                    'nb_formations'  => $a->formations_count,
                    'date_adhesion'  => $dateAdhesion,
                ];
            }),
        ]);
    }
}