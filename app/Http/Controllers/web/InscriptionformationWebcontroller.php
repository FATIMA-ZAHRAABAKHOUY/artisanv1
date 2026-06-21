<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InscriptionFormation;
use App\Models\Formation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// ================================================================
//  InscriptionFormationWebController
//  Controller Web (Blade) — Inscriptions aux formations
// ================================================================
class InscriptionFormationWebController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /formations/mes-inscriptions
    // Vue : formations/mes-inscriptions.blade.php
    // ────────────────────────────────────────────────────────────
    public function mesInscriptions(Request $request): View
    {
        $inscriptions = InscriptionFormation::with([
            'formation.artisan.user',
        ])
        ->where('apprenant_id', auth()->id())
        ->when($request->filled('statut'),
            fn($q) => $q->where('statut_inscription', $request->statut)
        )
        ->latest('date_inscription')
        ->paginate(10);

        // Résumé pour les onglets
        $resume = [
            'en_cours'   => InscriptionFormation::where('apprenant_id', auth()->id())
                                ->where('statut_inscription', 'en_cours')->count(),
            'terminees'  => InscriptionFormation::where('apprenant_id', auth()->id())
                                ->where('statut_inscription', 'terminee')->count(),
            'abandonnees'=> InscriptionFormation::where('apprenant_id', auth()->id())
                                ->where('statut_inscription', 'abandonnee')->count(),
        ];

        return view('formations.mes-inscriptions', compact('inscriptions', 'resume'));
    }

    // ────────────────────────────────────────────────────────────
    // POST /formations/{id}/inscrire
    // Inscription à une formation
    // OCL : 1 seule formation active à la fois
    // ────────────────────────────────────────────────────────────
    public function inscrire(int $formationId): RedirectResponse
    {
        $formation = Formation::with(['artisan.user'])->findOrFail($formationId);

        // ── Vérifications ─────────────────────────────────────

        // 1. Formation active
        if (!$formation->is_active) {
            return back()->with('error', 'Cette formation n\'est plus disponible.');
        }

        // 2. Formation pas encore passée
        if ($formation->date_fin < now()->toDateString()) {
            return back()->with('error', 'Cette formation est déjà terminée.');
        }

        // 3. Places disponibles
        if ($formation->estComplete()) {
            return back()->with('error',
                'Cette formation est complète. Aucune place disponible.'
            );
        }

        // 4. OCL : 1 seule formation active à la fois
        $formationActive = InscriptionFormation::where('apprenant_id', auth()->id())
            ->where('statut_inscription', 'en_cours')
            ->with('formation')
            ->first();

        if ($formationActive) {
            return back()->with('error',
                "Vous êtes déjà inscrit à « {$formationActive->formation->titre} ». "
                . 'Terminez ou abandonnez cette formation avant de vous inscrire à une autre.'
            );
        }

        // 5. Déjà inscrit à cette formation
        $dejaInscrit = InscriptionFormation::where([
            'formation_id' => $formationId,
            'apprenant_id' => auth()->id(),
        ])->first();

        if ($dejaInscrit) {
            return back()->with('error',
                "Vous êtes déjà inscrit à cette formation "
                . "(statut : {$dejaInscrit->statut_inscription})."
            );
        }

        // ── Créer l'inscription ───────────────────────────────
        $inscription = InscriptionFormation::create([
            'formation_id'       => $formationId,
            'apprenant_id'       => auth()->id(),
            'statut_inscription' => 'en_cours',
            'progression'        => 0,
            'date_inscription'   => now(),
            'date_debut_reelle'  => $formation->date_debut,
        ]);

        // ── Notifications ─────────────────────────────────────
        Notification::envoyer(
            auth()->id(),
            'inscription_formation',
            '🎓 Inscription confirmée',
            "Vous êtes inscrit à « {$formation->titre} » — "
            . $formation->date_debut->format('d/m/Y')
            . ' à ' . $formation->lieu,
            ['formation_id' => $formationId, 'inscription_id' => $inscription->id]
        );

        Notification::envoyer(
            $formation->artisan->user_id,
            'nouvel_inscrit',
            '👤 Nouvel inscrit',
            auth()->user()->nom_complet
            . " s'est inscrit à votre formation « {$formation->titre} ».",
            ['formation_id' => $formationId, 'inscription_id' => $inscription->id]
        );

        return redirect()
            ->route('formations.mes-inscriptions')
            ->with('success',
                "✅ Inscription confirmée pour « {$formation->titre} » ! "
                . "({$formation->placesDisponibles()} place(s) restante(s))"
            );
    }

    // ────────────────────────────────────────────────────────────
    // PUT /formations/inscriptions/{id}/abandonner
    // L'apprenant abandonne sa formation
    // ────────────────────────────────────────────────────────────
    public function abandonner(int $id): RedirectResponse
    {
        $inscription = InscriptionFormation::with('formation')
            ->where('apprenant_id', auth()->id())
            ->findOrFail($id);

        if (!$inscription->estEnCours()) {
            return back()->with('error',
                'Cette inscription n\'est pas en cours '
                . "(statut actuel : {$inscription->statut_inscription})."
            );
        }

        $inscription->update([
            'statut_inscription' => 'abandonnee',
            'date_fin_reelle'    => now()->toDateString(),
        ]);

        return redirect()
            ->route('formations.mes-inscriptions')
            ->with('success',
                "Formation « {$inscription->formation->titre} » abandonnée. "
                . 'Vous pouvez maintenant vous inscrire à une autre formation.'
            );
    }

    // ────────────────────────────────────────────────────────────
    // GET /formations/{id}/inscrits
    // Artisan — Liste des inscrits à sa formation
    // Vue : artisan/inscrits.blade.php
    // ────────────────────────────────────────────────────────────
    public function inscrits(Request $request, int $formationId): View
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);

        $inscrits = InscriptionFormation::with('apprenant')
            ->where('formation_id', $formationId)
            ->when($request->filled('statut'),
                fn($q) => $q->where('statut_inscription', $request->statut)
            )
            ->orderBy('date_inscription')
            ->paginate(15);

        $stats = [
            'en_cours'            => InscriptionFormation::where('formation_id', $formationId)
                                        ->where('statut_inscription','en_cours')->count(),
            'terminees'           => InscriptionFormation::where('formation_id', $formationId)
                                        ->where('statut_inscription','terminee')->count(),
            'abandonnees'         => InscriptionFormation::where('formation_id', $formationId)
                                        ->where('statut_inscription','abandonnee')->count(),
            'progression_moyenne' => round(
                InscriptionFormation::where('formation_id', $formationId)
                    ->where('statut_inscription','en_cours')
                    ->avg('progression') ?? 0, 1
            ),
        ];

        return view('artisan.inscrits', compact('formation', 'inscrits', 'stats'));
    }

    // ────────────────────────────────────────────────────────────
    // PUT /formations/inscriptions/{id}/progression
    // Artisan — Mettre à jour la progression d'un inscrit
    // OCL : 100% → terminée automatiquement
    // ────────────────────────────────────────────────────────────
    public function updateProgression(Request $request, int $id): RedirectResponse
    {
        $inscription = InscriptionFormation::with(['formation.artisan', 'apprenant'])
            ->findOrFail($id);

        // Vérifier artisan propriétaire
        if ($inscription->formation->artisan->user_id !== auth()->id()
            && !auth()->user()->isAdmin()) {
            abort(403, 'Accès refusé.');
        }

        $validated = $request->validate([
            'progression' => 'required|integer|min:0|max:100',
        ]);

        if (!$inscription->estEnCours()) {
            return back()->with('error',
                'Cette inscription est déjà '
                . $inscription->statut_inscription . '.'
            );
        }

        $inscription->mettreAJourProgression($validated['progression']);

        // Notification apprenant si 100%
        if ($inscription->estTerminee()) {
            Notification::envoyer(
                $inscription->apprenant_id,
                'formation_terminee',
                '🎓 Formation terminée !',
                "Félicitations ! Vous avez terminé « {$inscription->formation->titre} ».",
                ['formation_id' => $inscription->formation_id]
            );

            return back()->with('success',
                "🎓 {$inscription->apprenant->nom_complet} a terminé la formation !"
            );
        }

        return back()->with('success',
            "Progression de {$inscription->apprenant->nom_complet} "
            . "mise à jour : {$inscription->progression}%"
        );
    }

    // ────────────────────────────────────────────────────────────
    // POST /formations/inscriptions/{id}/certificat
    // Artisan/Admin — Délivrer un certificat
    // ────────────────────────────────────────────────────────────
    public function delivrerCertificat(Request $request, int $id): RedirectResponse
    {
        $inscription = InscriptionFormation::with(['formation','apprenant'])
            ->findOrFail($id);

        // Vérifier artisan ou admin
        if (!auth()->user()->isAdmin()) {
            if ($inscription->formation->artisan->user_id !== auth()->id()) {
                abort(403, 'Accès refusé.');
            }
        }

        if (!$inscription->estTerminee()) {
            return back()->with('error',
                'Le certificat ne peut être délivré que pour une formation terminée.'
            );
        }

        $validated = $request->validate([
            'note_finale'    => 'nullable|numeric|min:0|max:20',
            'certificat_file'=> 'nullable|file|mimes:pdf|max:5120',
            'certificat_url' => 'nullable|url',
        ]);

        $certUrl = $validated['certificat_url'] ?? null;

        // Upload fichier PDF
        if ($request->hasFile('certificat_file')) {
            $certUrl = $request->file('certificat_file')
                ->store("certificats/{$inscription->formation_id}", 'public');
            $certUrl = asset('storage/' . $certUrl);
        }

        if (!$certUrl) {
            return back()->with('error', 'Fournissez un fichier PDF ou une URL pour le certificat.');
        }

        $inscription->update([
            'certificat_url' => $certUrl,
            'note_finale'    => $validated['note_finale'] ?? null,
        ]);

        Notification::envoyer(
            $inscription->apprenant_id,
            'certificat_disponible',
            '🏅 Votre certificat est disponible',
            "Votre certificat de la formation « {$inscription->formation->titre} » "
            . 'est maintenant disponible. Téléchargez-le depuis votre espace.',
            ['inscription_id' => $inscription->id, 'certificat_url' => $certUrl]
        );

        return back()->with('success',
            "Certificat délivré à {$inscription->apprenant->nom_complet} !"
        );
    }

    // ────────────────────────────────────────────────────────────
    // Admin — Toutes les inscriptions
    // GET /admin/inscriptions
    // Vue : admin/inscriptions.blade.php
    // ────────────────────────────────────────────────────────────
    public function adminIndex(Request $request): View
    {
        $inscriptions = InscriptionFormation::with([
            'formation.artisan.user',
            'apprenant',
        ])
        ->when($request->filled('statut'),
            fn($q) => $q->where('statut_inscription', $request->statut)
        )
        ->when($request->filled('formation_id'),
            fn($q) => $q->where('formation_id', $request->formation_id)
        )
        ->latest('date_inscription')
        ->paginate(20);

        $stats = [
            'total'       => InscriptionFormation::count(),
            'en_cours'    => InscriptionFormation::where('statut_inscription','en_cours')->count(),
            'terminees'   => InscriptionFormation::where('statut_inscription','terminee')->count(),
            'abandonnees' => InscriptionFormation::where('statut_inscription','abandonnee')->count(),
        ];

        $formations = Formation::where('is_active', true)
            ->orderBy('titre')
            ->pluck('titre', 'id');

        return view('admin.inscriptions', compact('inscriptions', 'stats', 'formations'));
    }

    // ────────────────────────────────────────────────────────────
    // Admin — Suspendre une inscription
    // PUT /admin/inscriptions/{id}/suspendre
    // ────────────────────────────────────────────────────────────
    public function suspendre(int $id): RedirectResponse
    {
        $inscription = InscriptionFormation::with(['formation','apprenant'])
            ->findOrFail($id);

        if ($inscription->statut_inscription === 'terminee') {
            return back()->with('error', 'Impossible de suspendre une inscription terminée.');
        }

        $inscription->update(['statut_inscription' => 'suspendue']);

        Notification::envoyer(
            $inscription->apprenant_id,
            'inscription_suspendue',
            '⏸️ Inscription suspendue',
            "Votre inscription à « {$inscription->formation->titre} » a été suspendue.",
            ['inscription_id' => $inscription->id]
        );

        return back()->with('success',
            "Inscription de {$inscription->apprenant->nom_complet} suspendue."
        );
    }
}