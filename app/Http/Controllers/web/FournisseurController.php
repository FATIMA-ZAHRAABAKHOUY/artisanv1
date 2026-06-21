<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\Formation;
use App\Models\FournisseurMateriau;
use App\Models\FournisseurOutil;
use App\Models\SuggestionAchat;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $query = Fournisseur::where('statut', 'actif');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $query->where('nom', 'ilike', '%' . $request->q . '%');
        }

        $fournisseurs = $query->orderByDesc('note_moyenne')->orderBy('nom')->paginate(12);

        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function show(int $id)
    {
        $fournisseur = Fournisseur::where('statut', 'actif')
            ->with(['materiaux.materiau.formation', 'outils.outil.formation'])
            ->findOrFail($id);

        return view('fournisseurs.show', compact('fournisseur'));
    }

    public function suggestionsPourFormation(int $formationId)
    {
        $formation = Formation::with([
            'materiaux.fournisseurs.fournisseur',
            'outils.fournisseurs.fournisseur',
        ])->findOrFail($formationId);

        return view('fournisseurs.suggestions', compact('formation'));
    }

    public function trackClick(Request $request, int $fournisseurId)
    {
        if (! auth()->check() || ! auth()->user()->isApprenant()) {
            return response()->json(['ok' => false]);
        }

        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'type_objet'   => 'required|in:materiau,outil',
            'objet_id'     => 'required|integer',
        ]);

        $fournisseur = Fournisseur::where('id', $fournisseurId)
            ->where('statut', 'actif')
            ->first();

        if (! $fournisseur) {
            return response()->json(['ok' => false]);
        }

        $formationId = (int) $validated['formation_id'];
        $objetId     = (int) $validated['objet_id'];

        if ($validated['type_objet'] === 'materiau') {
            $produit = FournisseurMateriau::where('id', $objetId)
                ->where('fournisseur_id', $fournisseur->id)
                ->whereHas('materiau', fn ($q) => $q->where('formation_id', $formationId))
                ->exists();
        } else {
            $produit = FournisseurOutil::where('id', $objetId)
                ->where('fournisseur_id', $fournisseur->id)
                ->whereHas('outil', fn ($q) => $q->where('formation_id', $formationId))
                ->exists();
        }

        if (! $produit) {
            return response()->json(['ok' => false]);
        }

        try {
            SuggestionAchat::updateOrCreate(
                [
                    'apprenant_id'   => auth()->id(),
                    'formation_id'   => $formationId,
                    'fournisseur_id' => $fournisseur->id,
                    'type_objet'     => $validated['type_objet'],
                    'objet_id'       => $objetId,
                ],
                ['est_clique' => true, 'created_at' => now()]
            );
        } catch (\Throwable) {
            // Analytics only — do not block navigation
        }

        return response()->json(['ok' => true]);
    }
}
