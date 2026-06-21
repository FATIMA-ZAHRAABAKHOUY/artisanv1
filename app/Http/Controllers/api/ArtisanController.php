<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ArtisanController extends Controller
{
    // GET /api/artisans (public)
    public function index(Request $request): JsonResponse
    {
        $artisans = Artisan::with('user')
            ->where('is_verified', true)
            ->where('statut', 'actif')
            ->when($request->filled('specialite'), fn($q) =>
                $q->where('specialite', 'ilike', "%{$request->specialite}%")
            )
            ->when($request->filled('region'), fn($q) =>
                $q->whereHas('user', fn($u) => $u->where('ville', 'ilike', "%{$request->region}%"))
            )
            ->withCount('produits')
            ->orderByDesc('note_moyenne')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data'    => $artisans->map(fn($a) => [
                'id'           => $a->id,
                'nom'          => $a->user->nom_complet,
                'specialite'   => $a->specialite,
                'bio'          => $a->bio,
                'region'       => $a->user->ville,
                'note_moyenne' => $a->note_moyenne,
                'nb_produits'  => $a->produits_count,
                'avatar'       => $a->user->avatar_url,
                'date_adhesion'=> $a->date_adhesion?->format('d/m/Y'),
            ]),
        ]);
    }

    // GET /api/artisans/{id} (public)
    public function show(int $id): JsonResponse
    {
        $artisan = Artisan::with(['user', 'produits' => fn($q) => $q->where('is_active', true)])
                          ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $artisan->id,
                'nom'             => $artisan->user->nom_complet,
                'specialite'      => $artisan->specialite,
                'bio'             => $artisan->bio,
                'experience'      => $artisan->experience_annees,
                'region'          => $artisan->user->ville,
                'note_moyenne'    => $artisan->note_moyenne,
                'is_verified'     => $artisan->is_verified,
                'date_adhesion'   => $artisan->date_adhesion?->format('d/m/Y'),
                'avatar'          => $artisan->user->avatar_url,
                'nb_produits'     => $artisan->produits->count(),
                'produits'        => $artisan->produits->take(8)->map(fn($p) => [
                    'id'    => $p->id,
                    'nom'   => $p->nom,
                    'prix'  => $p->prix,
                    'image' => isset($p->images[0]) ? asset("storage/{$p->images[0]}") : null,
                ]),
            ],
        ]);
    }

    // GET /api/artisan/profil (artisan connecté)
    public function profil(Request $request): JsonResponse
    {
        $artisan = $request->user()->artisan->load(['user', 'fournisseurs.specialites']);

        return response()->json(['success' => true, 'data' => $this->profilComplet($artisan)]);
    }

    // PUT /api/artisan/profil
    public function updateProfil(Request $request): JsonResponse
    {
        $artisan = $request->user()->artisan;

        $validated = $request->validate([
            'specialite'        => 'sometimes|string|max:100',
            'bio'               => 'nullable|string|max:1000',
            'experience_annees' => 'sometimes|integer|min:0',
            'rib'               => 'nullable|string|max:30',
        ]);

        $artisan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil artisan mis à jour.',
            'data'    => $this->profilComplet($artisan->fresh()),
        ]);
    }

    // GET /api/artisan/mes-produits
    public function mesProduits(Request $request): JsonResponse
    {
        $produits = $request->user()->artisan->produits()
            ->with('categorie')
            ->when($request->filled('actif'), fn($q) => $q->where('is_active', $request->actif))
            ->latest()
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $produits]);
    }

    // GET /api/artisan/mes-commandes
    public function mesCommandes(Request $request): JsonResponse
    {
        $artisanId = $request->user()->artisan->id;

        $commandes = DB::table('commandes')
            ->join('lignes_commande', 'lignes_commande.commande_id', '=', 'commandes.id')
            ->join('produits',        'produits.id',       '=', 'lignes_commande.produit_id')
            ->join('users',           'users.id',          '=', 'commandes.client_id')
            ->where('produits.artisan_id', $artisanId)
            ->when($request->filled('statut'), fn($q) => $q->where('commandes.statut', $request->statut))
            ->selectRaw("DISTINCT commandes.id, commandes.statut,
                         commandes.total_ttc, commandes.created_at,
                         users.nom || ' ' || users.prenom as client")
            ->orderByDesc('commandes.created_at')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $commandes]);
    }

    // GET /api/artisan/mes-revenus
    public function mesRevenus(Request $request): JsonResponse
    {
        $artisanId = $request->user()->artisan->id;

        $revenus = DB::table('lignes_commande')
            ->join('produits',  'produits.id',  '=', 'lignes_commande.produit_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->where('produits.artisan_id', $artisanId)
            ->where('commandes.statut', 'delivered')
            ->selectRaw("SUM(lignes_commande.sous_total) as total_revenus,
                         COUNT(DISTINCT commandes.id) as nb_commandes,
                         COUNT(lignes_commande.id) as nb_articles_vendus")
            ->first();

        $revenusParMois = DB::table('lignes_commande')
            ->join('produits',  'produits.id',  '=', 'lignes_commande.produit_id')
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->where('produits.artisan_id', $artisanId)
            ->where('commandes.statut', 'delivered')
            ->where('commandes.created_at', '>=', now()->subMonths(6))
            ->selectRaw("TO_CHAR(DATE_TRUNC('month', commandes.created_at), 'YYYY-MM') as mois,
                         SUM(lignes_commande.sous_total) as revenus")
            ->groupBy(DB::raw("DATE_TRUNC('month', commandes.created_at)"))
            ->orderBy(DB::raw("DATE_TRUNC('month', commandes.created_at)"))
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'resume'          => $revenus,
                'revenus_par_mois'=> $revenusParMois,
            ],
        ]);
    }

    // GET /api/artisan/mes-formations
    public function mesFormations(Request $request): JsonResponse
    {
        $formations = $request->user()->artisan->formations()
            ->withCount(['inscriptions', 'inscriptions as en_cours' => fn ($q) => $q->where('statut_inscription', 'en_cours')])
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $formations]);
    }

    // GET /api/admin/artisans/en-attente
    public function enAttente(): JsonResponse
    {
        $artisans = Artisan::with('user')
            ->where('is_verified', false)
            ->where('statut', 'actif')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $artisans->map(fn($a) => [
                'id'           => $a->id,
                'nom'          => $a->user->nom_complet,
                'email'        => $a->user->email,
                'specialite'   => $a->specialite,
                'cin'          => $a->cin,
                'date_adhesion'=> $a->date_adhesion?->format('d/m/Y'),
            ]),
        ]);
    }

    // PUT /api/admin/artisans/{id}/valider
    public function valider(int $id): JsonResponse
    {
        $artisan = Artisan::with('user')->findOrFail($id);
        $artisan->update(['is_verified' => true, 'statut' => 'actif']);

        Notification::envoyer(
            $artisan->user_id,
            'artisan_valide',
            '✅ Compte artisan validé',
            'Votre compte artisan a été validé par la coopérative. Vous pouvez maintenant publier vos produits.',
            ['artisan_id' => $artisan->id]
        );

        return response()->json([
            'success' => true,
            'message' => "Artisan {$artisan->user->nom_complet} validé.",
        ]);
    }

    // PUT /api/admin/artisans/{id}/suspendre
    public function suspendre(Request $request, int $id): JsonResponse
    {
        $artisan = Artisan::with('user')->findOrFail($id);
        $artisan->update(['statut' => 'suspendu']);
        $artisan->user->update(['statut' => 'suspendu']);

        Notification::envoyer(
            $artisan->user_id,
            'artisan_suspendu',
            'Compte artisan suspendu',
            'Votre compte artisan a été suspendu. Contactez l\'administrateur pour plus d\'informations.',
            ['artisan_id' => $artisan->id]
        );

        return response()->json([
            'success' => true,
            'message' => "Artisan {$artisan->user->nom_complet} suspendu.",
        ]);
    }

    private function profilComplet(Artisan $a): array
    {
        return [
            'id'                => $a->id,
            'nom'               => $a->user->nom_complet,
            'email'             => $a->user->email,
            'telephone'         => $a->user->telephone,
            'ville'             => $a->user->ville,
            'avatar'            => $a->user->avatar_url,
            'specialite'        => $a->specialite,
            'bio'               => $a->bio,
            'experience_annees' => $a->experience_annees,
            'cin'               => $a->cin,
            'rib'               => $a->rib,
            'note_moyenne'      => $a->note_moyenne,
            'statut'            => $a->statut,
            'is_verified'       => $a->is_verified,
            'date_adhesion'     => $a->date_adhesion?->format('d/m/Y'),
            'fournisseurs'      => $a->relationLoaded('fournisseurs')
                ? $a->fournisseurs->map(fn($f) => [
                    'id'            => $f->id,
                    'nom'           => $f->nom,
                    'type'          => $f->type,
                    'est_principal' => $f->pivot->est_principal,
                ])
                : [],
        ];
    }
}
