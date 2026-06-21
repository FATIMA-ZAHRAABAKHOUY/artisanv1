<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FournisseurMateriau;
use App\Models\FournisseurOutil;
use App\Models\SuggestionAchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FournisseurEspaceController extends Controller
{
    public function dashboard()
    {
        $fournisseur = auth()->user()->fournisseur;

        if (! $fournisseur) {
            abort(403, 'Profil fournisseur introuvable.');
        }

        $nbMateriaux = FournisseurMateriau::where('fournisseur_id', $fournisseur->id)->count();
        $nbOutils    = FournisseurOutil::where('fournisseur_id', $fournisseur->id)->count();

        $nbClics = 0;
        $nbAchats = 0;
        $recentActivity = collect();

        if (Schema::hasTable('suggestion_achat')) {
            $nbClics = SuggestionAchat::where('fournisseur_id', $fournisseur->id)
                ->where('est_clique', true)->count();
            $nbAchats = SuggestionAchat::where('fournisseur_id', $fournisseur->id)
                ->where('est_achete', true)->count();

            $recentActivity = SuggestionAchat::where('fournisseur_id', $fournisseur->id)
                ->where('est_clique', true)
                ->with('formation')
                ->latest('created_at')
                ->take(6)
                ->get();
        }

        return view('fournisseur.dashboard', compact(
            'fournisseur', 'nbMateriaux', 'nbOutils', 'nbClics', 'nbAchats', 'recentActivity'
        ));
    }

    public function produits()
    {
        $fournisseur = auth()->user()->fournisseur;

        $materiaux = FournisseurMateriau::where('fournisseur_id', $fournisseur->id)
            ->with('materiau.formation')
            ->latest()
            ->paginate(10, ['*'], 'materiaux_page');

        $outils = FournisseurOutil::where('fournisseur_id', $fournisseur->id)
            ->with('outil.formation')
            ->latest()
            ->paginate(10, ['*'], 'outils_page');

        return view('fournisseur.produits', compact('fournisseur', 'materiaux', 'outils'));
    }

    public function updateMateriau(Request $request, int $id)
    {
        $fournisseur = auth()->user()->fournisseur;

        $produit = FournisseurMateriau::where('fournisseur_id', $fournisseur->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'prix_unitaire'       => 'required|numeric|min:0',
            'unite_prix'          => 'nullable|string|max:50',
            'url_produit'         => 'nullable|url|max:500',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
        ]);

        $validated['stock_disponible'] = $request->boolean('stock_disponible');

        $produit->update($validated);

        return back()->with('success', 'Produit mis à jour.');
    }

    public function updateOutil(Request $request, int $id)
    {
        $fournisseur = auth()->user()->fournisseur;

        $produit = FournisseurOutil::where('fournisseur_id', $fournisseur->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'prix_unitaire'       => 'required|numeric|min:0',
            'unite_prix'          => 'nullable|string|max:50',
            'url_produit'         => 'nullable|url|max:500',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
        ]);

        $validated['stock_disponible'] = $request->boolean('stock_disponible');

        $produit->update($validated);

        return back()->with('success', 'Produit mis à jour.');
    }

    public function profil()
    {
        $fournisseur = auth()->user()->fournisseur;

        return view('fournisseur.profil', compact('fournisseur'));
    }

    public function updateProfil(Request $request)
    {
        $fournisseur = auth()->user()->fournisseur;

        $validated = $request->validate([
            'telephone' => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:300',
            'ville'     => 'nullable|string|max:100',
            'site_web'  => 'nullable|url|max:300',
            'logo'      => 'nullable|image|max:2048',
        ]);

        $siteWeb = $validated['site_web'] ?? $fournisseur->site_web;
        if ($fournisseur->type === 'en_ligne' && empty($siteWeb)) {
            return back()->withErrors([
                'site_web' => 'Le site web est obligatoire pour un fournisseur en ligne.',
            ])->withInput();
        }

        if ($request->hasFile('logo')) {
            if ($fournisseur->logo) {
                Storage::disk('public')->delete($fournisseur->logo);
            }
            $validated['logo'] = $request->file('logo')->store('fournisseurs', 'public');
        }

        $fournisseur->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
