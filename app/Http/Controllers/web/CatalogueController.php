<?php

// app/Http/Controllers/Web/CatalogueController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogueController extends Controller
{
    /**
     * Liste des produits avec recherche multi-critères
     */
    public function index(Request $request)
    {
        // On récupère les catégories parentes avec leurs enfants pour l'affichage du menu de filtrage
        $categoriesSide = Categorie::whereNull('parent_id')->with('sousCategories')->get();

        $query = Produit::with(['artisan', 'categorie']) // Simplifié si la relation directe artisan suffit
            ->where('is_active', true);

        // Filtre Recherche Textuelle (Nom / Description)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('nom', 'ilike', "%{$q}%")
                   ->orWhere('description', 'ilike', "%{$q}%");
            });
        }

        // Filtre par catégorie (Gère aussi les sous-catégories si une catégorie parente est sélectionnée)
        if ($request->filled('categorie_id')) {
            $catId = $request->categorie_id;
            
            // On cherche si cette catégorie a des enfants
            $ids = Categorie::where('id', $catId)
                ->orWhere('parent_id', $catId)
                ->pluck('id');

            $query->whereIn('categorie_id', $ids);
        }

        // Filtres de prix
        if ($request->filled('prix_min')) {
            $query->where('prix', '>=', $request->prix_min);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        // Filtre Stock
        if ($request->filled('en_stock')) {
            $query->where('stock', '>', 0);
        }

        // Tri dynamique
        $sortMap = [
            'prix'       => ['prix', 'asc'],
            'prix_desc'  => ['prix', 'desc'],
            'nom'        => ['nom', 'asc'],
            'created_at' => ['created_at', 'desc'],
        ];
        [$col, $dir] = $sortMap[$request->get('sort', 'created_at')] ?? ['created_at', 'desc'];
        $query->orderBy($col, $dir);

        $produits = $query->paginate(12)->withQueryString();

        return view('catalogue.index', compact('produits', 'categoriesSide'));
    }

    /**
     * Fiche détail d'un produit
     */
    public function show(int $id)
    {
        $produit = Produit::with([
            'artisan',
            'categorie',
            'avis.client',
        ])->where('is_active', true)->findOrFail($id);

        $noteMoyenne = round($produit->avis->avg('note') ?? 0, 1);

        // Produits similaires (même catégorie, hors produit actuel)
        $similaires = Produit::with(['artisan'])
            ->where('is_active', true)
            ->where('categorie_id', $produit->categorie_id)
            ->where('id', '!=', $produit->id)
            ->take(4)
            ->get();

        return view('catalogue.show', compact('produit', 'noteMoyenne', 'similaires'));
    }

    /**
     * Filtrage direct depuis un clic sur un lien de catégorie (gère l'héritage parent-enfant)
     */
    public function categorie(string $slug)
    {
        $categorie = Categorie::where('slug', $slug)->firstOrFail();
        $categoriesSide = Categorie::whereNull('parent_id')->with('sousCategories')->get();

        // Récupérer l'ID de la catégorie et ceux de ses éventuelles sous-catégories
        $categorieIds = Categorie::where('id', $categorie->id)
            ->orWhere('parent_id', $categorie->id)
            ->pluck('id');

        $produits = Produit::with(['artisan', 'categorie'])
            ->where('is_active', true)
            ->whereIn('categorie_id', $categorieIds) // Utilisation de whereIn au lieu de where
            ->paginate(12);

        return view('catalogue.index', compact('produits', 'categorie', 'categoriesSide'));
    }

    /**
     * Soumission d'un avis client après réception
     */
    public function ajouterAvis(Request $request, int $id)
    {
        $produit = Produit::findOrFail($id);

        $validated = $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        // Vérification stricte de l'achat et de la livraison
        $aAchete = auth()->user()->commandes()
            ->where('statut', 'delivered') // Correspond à votre ENUM de livraison livrée
            ->whereHas('lignes', fn($q) => $q->where('produit_id', $id))
            ->exists();

        if (!$aAchete) {
            return back()->with('error', 'Vous devez avoir reçu ce produit pour laisser un avis.');
        }

        // Ajout ou mise à jour de l'avis
        \App\Models\Avis::updateOrCreate(
            ['produit_id' => $id, 'client_id' => auth()->id()],
            $validated
        );

        // Recalcul de la note de la coopérative/artisan concerné
        $note = DB::table('avis')
            ->join('produits', 'produits.id', '=', 'avis.produit_id')
            ->where('produits.artisan_id', $produit->artisan_id)
            ->avg('avis.note');

        \App\Models\Artisan::where('id', $produit->artisan_id)
            ->update(['note_moyenne' => round($note ?? 0, 2)]);

        return back()->with('success', 'Votre avis a été publié. Merci !');
    }
}