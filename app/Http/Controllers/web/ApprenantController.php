<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EtapeFormation;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Notification;
use App\Models\RessourceFormation;
use Illuminate\Support\Facades\DB;

class ApprenantController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $formationActive = InscriptionFormation::where('apprenant_id', $user->id)
            ->where('statut_inscription', 'en_cours')
            ->with(['formation.artisan.user', 'formation.etapes'])
            ->first();

        $nbTerminees = InscriptionFormation::where('apprenant_id', $user->id)
            ->where('statut_inscription', 'terminee')
            ->count();

        $nbRessources = 0;
        $etapes = collect();
        $etapesTerminees = 0;
        $joursRestants = 0;
        $fournisseurs = collect();
        $ressources = collect();

        if ($formationActive) {
            $nbRessources = RessourceFormation::where('formation_id', $formationActive->formation_id)->count();

            $etapes = EtapeFormation::where('formation_id', $formationActive->formation_id)
                ->orderBy('numero_ordre')
                ->get();

            $etapesTerminees = (int) floor(($formationActive->progression / 100) * max($etapes->count(), 1));

            $joursRestants = max(0, (int) now()->diffInDays($formationActive->formation?->date_fin, false));

            $ressources = RessourceFormation::where('formation_id', $formationActive->formation_id)
                ->orderBy('ordre')
                ->take(5)
                ->get();

            if (\Illuminate\Support\Facades\Schema::hasTable('fournisseur_materiaux')) {
                $fournisseurs = DB::table('fournisseur_materiaux')
                    ->join('fournisseurs', 'fournisseurs.id', '=', 'fournisseur_materiaux.fournisseur_id')
                    ->join('materiaux_formation', 'materiaux_formation.id', '=', 'fournisseur_materiaux.materiau_id')
                    ->where('materiaux_formation.formation_id', $formationActive->formation_id)
                    ->select('fournisseurs.*', 'fournisseur_materiaux.est_recommande')
                    ->distinct()
                    ->take(3)
                    ->get();
            }
        }

        $notifs = Notification::where('user_id', $user->id)->latest()->take(4)->get();

        $autresFormations = Formation::where('is_active', true)
            ->where('date_debut', '>=', now()->toDateString())
            ->with('artisan.user')
            ->withCount(['inscriptions as inscrits_actifs' => fn ($q) => $q->where('statut_inscription', 'en_cours')])
            ->take(3)
            ->get();

        return view('apprenant.dashboard', compact(
            'user', 'formationActive', 'nbTerminees', 'nbRessources',
            'etapes', 'etapesTerminees', 'joursRestants',
            'ressources', 'fournisseurs', 'notifs', 'autresFormations'
        ));
    }
}
