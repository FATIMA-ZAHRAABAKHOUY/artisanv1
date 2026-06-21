<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Artisan;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// ================================================================
//  FormationWebController
// ================================================================
class FormationWebController extends Controller
{
    // GET /formations
    public function index(Request $request)
    {
        $query = Formation::with(['artisan.user'])
            ->where('is_active', true);

        if ($request->filled('ville')) {
            $query->where('lieu', 'ilike', "%{$request->ville}%");
        }
        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }
        if ($request->filled('gratuit')) {
            $query->where('prix', 0);
        }
        if ($request->filled('a_venir')) {
            $query->where('date_debut', '>=', now()->toDateString());
        }

        $formations = $query->orderBy('date_debut')->paginate(12);

        return view('formations.index', compact('formations'));
    }

    // GET /formations/{id}
    // CORRECTION : Suppression du typage strict "int" pour éviter l'erreur de routage de Laravel
    public function show($id)
    {
        $formation = Formation::with([
            'artisan.user',
            'formateurs.user',
            'etapes',
            'materiaux.fournisseurs.fournisseur',
            'outils.fournisseurs.fournisseur',
            'ressources',
        ])->where('is_active', true)->findOrFail($id);

        $estInscrit  = false;
        $inscription = null;

        if (auth()->check()) {
            $inscription = InscriptionFormation::where([
                'formation_id' => $id,
                'apprenant_id' => auth()->id(),
            ])->whereIn('statut_inscription', ['en_cours', 'terminee'])->first();

            $estInscrit = (bool) $inscription;
        }

        $estProprietaire = auth()->check()
            && auth()->user()->isArtisan()
            && auth()->user()->artisan?->id === $formation->artisan_id;

        $estAdmin = auth()->check() && auth()->user()->isAdmin();

        $peutVoirToutesRessources = $estInscrit || $estProprietaire || $estAdmin;

        $ressourcesAffichees = $peutVoirToutesRessources
            ? $formation->ressources
            : $formation->ressources->where('est_public', true)->values();

        $inscrits = $formation->inscriptions()
            ->where('statut_inscription', 'en_cours')
            ->count();

        return view('formations.show', compact(
            'formation',
            'estInscrit',
            'inscription',
            'inscrits',
            'ressourcesAffichees',
            'peutVoirToutesRessources',
        ));
    }

    // POST /formations/{id}/inscrire
    // CORRECTION : Suppression du typage "int"
    public function inscrire($id)
    {
        if (! auth()->user()->isApprenant()) {
            return back()->with('error',
                "Seuls les comptes apprenti peuvent s'inscrire à une formation. ".
                'Contactez la coopérative pour plus d\'informations.');
        }

        $formation = Formation::findOrFail($id);

        if ($formation->estComplete()) {
            return back()->with('error', 'Cette formation est complète.');
        }

        // OCL : 1 seule formation active
        $active = InscriptionFormation::where('apprenant_id', auth()->id())
            ->where('statut_inscription', 'en_cours')
            ->with('formation')
            ->first();

        if ($active) {
            return back()->with('error',
                "Vous êtes déjà inscrit à « {$active->formation->titre} ». "
                . "Terminez-la avant de vous inscrire à une autre."
            );
        }

        // Déjà inscrit ?
        $existe = InscriptionFormation::where([
            'formation_id' => $id,
            'apprenant_id' => auth()->id(),
        ])->exists();

        if ($existe) {
            return back()->with('error', 'Vous êtes déjà inscrit à cette formation.');
        }

        InscriptionFormation::create([
            'formation_id'       => $id,
            'apprenant_id'       => auth()->id(),
            'statut_inscription' => 'en_cours',
            'progression'        => 0,
            'date_inscription'   => now(),
            'date_debut_reelle'  => $formation->date_debut,
        ]);

        // Sécurisation du formatage de la date de début
        $dateDebutFormatee = $formation->date_debut 
            ? Carbon::parse($formation->date_debut)->format('d/m/Y') 
            : now()->format('d/m/Y');

        Notification::envoyer(
            auth()->id(),
            'inscription_formation',
            '🎓 Inscription confirmée',
            "Vous êtes inscrit à « {$formation->titre} » — " . $dateDebutFormatee,
            ['formation_id' => $id]
        );

        return back()->with('success',
            "Inscription confirmée pour « {$formation->titre} » !");
    }

    // PUT /formations/inscriptions/{id}/abandonner
    // CORRECTION : Suppression du typage "int"
    public function abandonner($inscriptionId)
    {
        if (! auth()->user()->isApprenant()) {
            return back()->with('error',
                "Seuls les comptes apprenti peuvent gérer leurs inscriptions.");
        }

        $inscription = InscriptionFormation::where('apprenant_id', auth()->id())
            ->findOrFail($inscriptionId);

        $inscription->update([
            'statut_inscription' => 'abandonnee',
            'date_fin_reelle'    => now()->toDateString(),
        ]);

        return redirect()->route('formations.mes-inscriptions')
            ->with('success', 'Formation abandonnée. Vous pouvez maintenant vous inscrire à une autre.');
    }

    // GET /formations/mes-inscriptions
    public function mesInscriptions(Request $request)
    {
        $inscriptions = InscriptionFormation::with(['formation.artisan.user'])
            ->where('apprenant_id', auth()->id())
            ->when($request->filled('statut'), fn($q) => $q->where('statut_inscription', $request->statut))
            ->latest('date_inscription')
            ->paginate(10);

        return view('formations.mes-inscriptions', compact('inscriptions'));
    }

    // GET /formations/{id}/ressources
    // CORRECTION : Suppression du typage "int"
    public function ressources($id)
    {
        $formation = Formation::findOrFail($id);

        $inscrit = InscriptionFormation::where([
            'formation_id' => $id,
            'apprenant_id' => auth()->id(),
        ])->whereIn('statut_inscription', ['en_cours', 'terminee'])->exists();

        if (!$inscrit) {
            return redirect()->route('formations.show', $id)
                ->with('error', 'Vous devez être inscrit pour accéder aux ressources.');
        }

        $ressources = $formation->ressources()->orderBy('ordre')->get();

        return view('formations.ressources', compact('formation', 'ressources'));
    }
}