<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategorieController extends Controller
{
    // GET /api/categories (public)
    public function index(): JsonResponse
    {
        $categories = Categorie::with('enfants')
            ->whereNull('parent_id')
            ->withCount('produits')
            ->orderBy('nom')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories->map(fn($c) => [
                'id'           => $c->id,
                'nom'          => $c->nom,
                'description'  => $c->description,
                'slug'         => $c->slug,
                'image'        => $c->image ? asset("storage/{$c->image}") : null,
                'nb_produits'  => $c->produits_count,
                'sous_categories' => $c->enfants->map(fn($e) => [
                    'id'  => $e->id,
                    'nom' => $e->nom,
                    'slug'=> $e->slug,
                ]),
            ]),
        ]);
    }

    // GET /api/categories/{id} (public)
    public function show(int $id): JsonResponse
    {
        $categorie = Categorie::with(['enfants', 'parent', 'produits' => fn($q) =>
            $q->where('is_active', true)->limit(20)
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $categorie]);
    }

    // POST /api/admin/categories
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100|unique:categories,nom',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $categorie = Categorie::create([
            ...$validated,
            'image' => $imagePath,
            'slug'  => Str::slug($validated['nom']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée.',
            'data'    => $categorie,
        ], 201);
    }

    // PUT /api/admin/categories/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);

        $validated = $request->validate([
            'nom'         => "sometimes|string|max:100|unique:categories,nom,{$id}",
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);

        if (isset($validated['nom'])) {
            $validated['slug'] = Str::slug($validated['nom']);
        }

        $categorie->update($validated);

        return response()->json(['success' => true, 'message' => 'Catégorie mise à jour.', 'data' => $categorie->fresh()]);
    }

    // DELETE /api/admin/categories/{id}
    public function destroy(int $id): JsonResponse
    {
        $categorie = Categorie::findOrFail($id);

        if ($categorie->produits()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => "Impossible de supprimer : {$categorie->produits()->count()} produit(s) associé(s).",
            ], 422);
        }

        $categorie->delete();

        return response()->json(['success' => true, 'message' => 'Catégorie supprimée.']);
    }
}

