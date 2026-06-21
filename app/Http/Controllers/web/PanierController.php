<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Livraison;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ================================================================
//  PanierController — Gestion panier en session
// ================================================================
class PanierController extends Controller
{
    // GET /panier
    public function index()
    {
        $panier = session('panier', []);
        return view('catalogue.panier', compact('panier'));
    }

    // POST /panier/{id} — Ajouter au panier
    public function ajouter(Request $request, int $id)
    {
        $produit  = Produit::where('is_active', true)->findOrFail($id);
        $quantite = max(1, (int) $request->get('quantite', 1));

        if ($produit->stock < $quantite) {
            return back()->with('error',
                "Stock insuffisant. Disponible : {$produit->stock}");
        }

        $panier = session('panier', []);

        if (isset($panier[$id])) {
            // Déjà dans le panier — incrémenter
            $nouvelleQte = $panier[$id]['quantite'] + $quantite;
            if ($nouvelleQte > $produit->stock) {
                $nouvelleQte = $produit->stock;
            }
            $panier[$id]['quantite'] = $nouvelleQte;
        } else {
            $panier[$id] = [
                'produit_id' => $produit->id,
                'nom'        => $produit->nom,
                'prix'       => $produit->prix,
                'quantite'   => $quantite,
                'image'      => $produit->images[0] ?? null,
                'artisan'    => $produit->artisan?->user?->nom_complet ?? '',
                'stock'      => $produit->stock,
            ];
        }

        session(['panier' => $panier]);
        session(['panier_count' => collect($panier)->sum('quantite')]);

        return back()->with('success', "« {$produit->nom} » ajouté au panier !");
    }

    // PUT /panier/{id} — Modifier quantité
    public function update(Request $request, int $id)
    {
        $panier = session('panier', []);

        if (!isset($panier[$id])) {
            return back()->with('error', 'Produit introuvable dans le panier.');
        }

        $action   = $request->get('action', 'plus');
        $quantite = $panier[$id]['quantite'];

        if ($action === 'plus') {
            $quantite++;
            // Vérifier stock
            $produit = Produit::find($id);
            if ($produit && $quantite > $produit->stock) {
                return back()->with('error', 'Stock maximum atteint.');
            }
        } else {
            $quantite--;
        }

        if ($quantite <= 0) {
            unset($panier[$id]);
        } else {
            $panier[$id]['quantite'] = $quantite;
        }

        session(['panier' => $panier]);
        session(['panier_count' => collect($panier)->sum('quantite')]);

        return back();
    }

    // DELETE /panier/{id} — Supprimer un article
    public function supprimer(int $id)
    {
        $panier = session('panier', []);
        unset($panier[$id]);
        session(['panier' => $panier]);
        session(['panier_count' => collect($panier)->sum('quantite')]);

        return back()->with('success', 'Article supprimé du panier.');
    }

    // DELETE /panier — Vider le panier
    public function vider()
    {
        session()->forget('panier');
        session()->forget('panier_count');

        return back()->with('success', 'Panier vidé.');
    }
}
