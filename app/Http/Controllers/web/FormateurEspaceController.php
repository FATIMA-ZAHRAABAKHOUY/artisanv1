<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FormateurEspaceController extends Controller
{
    public function dashboard()
    {
        $formateur = auth()->user()->formateur;

        if (! $formateur) {
            abort(403, 'Profil formateur introuvable.');
        }

        $formations = $formateur->formations()
            ->with('artisan.user')
            ->withCount(['inscriptions as inscrits_actifs' => function ($q) {
                $q->whereIn('statut', ['inscrit', 'confirme']);
            }])
            ->get();

        $nbFormationsActives = $formations->where('is_active', true)->count();
        $nbTotalInscrits     = $formations->sum('inscrits_actifs');

        return view('formateur.dashboard', compact(
            'formateur', 'formations', 'nbFormationsActives', 'nbTotalInscrits'
        ));
    }

    public function profil()
    {
        $formateur = auth()->user()->formateur;

        return view('formateur.profil', compact('formateur'));
    }

    public function updateProfil(Request $request)
    {
        $formateur = auth()->user()->formateur;

        $validated = $request->validate([
            'biographie'    => 'nullable|string',
            'diplomes'      => 'nullable|string',
            'langues'       => 'nullable|string|max:200',
            'tarif_journee' => 'nullable|numeric|min:0',
            'is_disponible' => 'nullable|boolean',
        ]);

        $validated['is_disponible'] = $request->boolean('is_disponible');

        $formateur->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
