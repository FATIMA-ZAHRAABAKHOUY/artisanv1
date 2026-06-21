<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with(['artisan.user', 'categorie']);

        // filtre catégorie
        if ($request->categorie_id) {
            $query->where('categorie_id', $request->categorie_id);
        }

        // filtre prix
        if ($request->prix_min) {
            $query->where('prix', '>=', $request->prix_min);
        }

        if ($request->prix_max) {
            $query->where('prix', '<=', $request->prix_max);
        }

        // stock
        if ($request->en_stock) {
            $query->where('stock', '>', 0);
        }

        // tri
        if ($request->sort) {
            if ($request->sort == 'prix') {
                $query->orderBy('prix');
            } elseif ($request->sort == 'nom') {
                $query->orderBy('nom');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $produits = $query->paginate(12);

        return view('catalogue.index', compact('produits'));
    }

    public function show($id)
    {
        $produit = Produit::with(['artisan.user', 'categorie'])->findOrFail($id);
        return view('catalogue.show', compact('produit'));
    }
}
