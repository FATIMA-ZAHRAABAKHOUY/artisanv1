<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Artisan;
use App\Models\Avis;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/produits
    // Public — liste avec filtres et pagination
    // ────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Produit::with(['artisan.user', 'categorie'])
                        ->where('is_active', true);

        // ── Filtres ──────────────────────────────────────────────
        if ($request->filled('q')) {
            $terme = $request->q;
            $query->where(function ($q) use ($terme) {
                $q->where('nom',          'ilike', "%{$terme}%")
                  ->orWhere('description','ilike', "%{$terme}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->filled('artisan_id')) {
            $query->where('artisan_id', $request->artisan_id);
        }

        if ($request->filled('prix_min')) {
            $query->where('prix', '>=', $request->prix_min);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        if ($request->filled('en_stock')) {
            $query->where('stock', '>', 0);
        }

        // ── Tri ──────────────────────────────────────────────────
        $sortOptions = ['prix', 'created_at', 'nom', 'stock'];
        $sort = in_array($request->sort, $sortOptions) ? $request->sort : 'created_at';
        $dir  = $request->dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $produits = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $produits->items(),
            'meta'    => [
                'total'        => $produits->total(),
                'per_page'     => $produits->perPage(),
                'current_page' => $produits->currentPage(),
                'last_page'    => $produits->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/produits/{id}
    // Public — détail produit avec avis
    // ────────────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $produit = Produit::with([
            'artisan.user',
            'categorie',
            'avis.client',
        ])->findOrFail($id);

        // Note moyenne
        $noteMoyenne = round($produit->avis->avg('note') ?? 0, 2);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $produit->id,
                'nom'          => $produit->nom,
                'description'  => $produit->description,
                'prix'         => $produit->prix,
                'stock'        => $produit->stock,
                'images'       => collect($produit->images ?? [])
                                    ->map(fn($img) => asset("storage/{$img}")),
                'poids'        => $produit->poids,
                'dimensions'   => $produit->dimensions,
                'slug'         => $produit->slug,
                'note_moyenne' => $noteMoyenne,
                'nb_avis'      => $produit->avis->count(),
                'categorie'    => $produit->categorie ? [
                    'id'  => $produit->categorie->id,
                    'nom' => $produit->categorie->nom,
                ] : null,
                'artisan' => $produit->artisan ? [
                    'id'           => $produit->artisan->id,
                    'nom'          => $produit->artisan->user->nom_complet,
                    'specialite'   => $produit->artisan->specialite,
                    'note_moyenne' => $produit->artisan->note_moyenne,
                    'is_verified'  => $produit->artisan->is_verified,
                ] : null,
                'avis' => $produit->avis->map(fn($a) => [
                    'note'        => $a->note,
                    'commentaire' => $a->commentaire,
                    'client'      => $a->client->nom_complet,
                    'date'        => $a->created_at?->format('d/m/Y'),
                ]),
                'created_at' => $produit->created_at,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/produits
    // Artisan actif uniquement
    // ────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'          => 'required|string|max:200',
            'description'  => 'nullable|string',
            'prix'         => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
            'poids'        => 'nullable|numeric|min:0',
            'dimensions'   => 'nullable|string|max:100',
            'images'       => 'nullable|array|max:5',
            'images.*'     => 'image|max:5120',
        ]);

        $artisan = $request->user()->artisan;

        // Upload images
        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imagesPaths[] = $img->store("produits/{$artisan->id}", 'public');
            }
        }

        $produit = Produit::create([
            'artisan_id'   => $artisan->id,
            'categorie_id' => $validated['categorie_id'] ?? null,
            'nom'          => $validated['nom'],
            'description'  => $validated['description'] ?? null,
            'prix'         => $validated['prix'],
            'stock'        => $validated['stock'],
            'images'       => $imagesPaths,
            'poids'        => $validated['poids'] ?? null,
            'dimensions'   => $validated['dimensions'] ?? null,
            'is_active'    => true,
            'slug'         => Str::slug($validated['nom']) . '-' . Str::random(6),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produit publié avec succès.',
            'data'    => $produit->load('categorie'),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/produits/{id}
    // Artisan propriétaire uniquement
    // ────────────────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $produit = Produit::findOrFail($id);

        // Vérifier propriété
        if ($produit->artisan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas le propriétaire de ce produit.',
            ], 403);
        }

        $validated = $request->validate([
            'nom'          => 'sometimes|string|max:200',
            'description'  => 'nullable|string',
            'prix'         => 'sometimes|numeric|min:0',
            'stock'        => 'sometimes|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
            'poids'        => 'nullable|numeric|min:0',
            'dimensions'   => 'nullable|string|max:100',
            'is_active'    => 'sometimes|boolean',
        ]);

        $produit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Produit mis à jour.',
            'data'    => $produit->fresh()->load('categorie'),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // DELETE /api/produits/{id}
    // Désactivation logique
    // ────────────────────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $produit = Produit::findOrFail($id);

        if ($produit->artisan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        $produit->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Produit désactivé.',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/produits/{id}/avis
    // Client ayant acheté le produit
    // ────────────────────────────────────────────────────────────
    public function ajouterAvis(Request $request, int $id): JsonResponse
    {
        $produit = Produit::findOrFail($id);
        $user    = $request->user();

        $validated = $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        // Vérifier achat
        $aAchete = $user->commandes()
            ->where('statut', 'delivered')
            ->whereHas('lignes', fn($q) => $q->where('produit_id', $id))
            ->exists();

        if (!$aAchete) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir reçu ce produit pour laisser un avis.',
            ], 403);
        }

        $avis = Avis::updateOrCreate(
            ['produit_id' => $id, 'client_id' => $user->id],
            $validated
        );

        // Recalculer note artisan
        $this->recalculerNoteArtisan($produit->artisan_id);

        return response()->json([
            'success' => true,
            'message' => 'Avis enregistré.',
            'data'    => $avis,
        ], 201);
    }

    // ── Helper privé ─────────────────────────────────────────────
    private function recalculerNoteArtisan(int $artisanId): void
    {
        $note = DB::table('avis')
            ->join('produits', 'produits.id', '=', 'avis.produit_id')
            ->where('produits.artisan_id', $artisanId)
            ->avg('avis.note');

        Artisan::where('id', $artisanId)
               ->update(['note_moyenne' => round($note ?? 0, 2)]);
    }
}