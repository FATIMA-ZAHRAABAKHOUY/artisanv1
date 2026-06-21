<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InscriptionFormation;
use App\Models\Formation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

// ================================================================
//  InscriptionFormationController
//  Gestion des inscriptions aux formations artisanales
//  Routes protégées : auth:sanctum + rôle apprenant/client/admin
// ================================================================
class InscriptionFormationController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/formations/mes-inscriptions
    // Retourne toutes les inscriptions de l'utilisateur connecté
    // ────────────────────────────────────────────────────────────
    public function mesInscriptions(Request $request): JsonResponse
    {
        $inscriptions = InscriptionFormation::with([
            'formation.artisan.user',
            'formation.formateurs.user',
        ])
        ->where('apprenant_id', $request->user()->id)
        ->when($request->filled('statut'),
            fn($q) => $q->where('statut_inscription', $request->statut)
        )
        ->latest('date_inscription')
        ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $inscriptions->map(fn($i) => $this->inscriptionResource($i)),
            'meta'    => [
                'total'        => $inscriptions->total(),
                'current_page' => $inscriptions->currentPage(),
                'last_page'    => $inscriptions->lastPage(),
            ],
            'resume' => [
                'en_cours'   => InscriptionFormation::where('apprenant_id', $request->user()->id)
                                    ->where('statut_inscription', 'en_cours')->count(),
                'terminees'  => InscriptionFormation::where('apprenant_id', $request->user()->id)
                                    ->where('statut_inscription', 'terminee')->count(),
                'abandonnees'=> InscriptionFormation::where('apprenant_id', $request->user()->id)
                                    ->where('statut_inscription', 'abandonnee')->count(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/inscriptions/{id}
    // Détail d'une inscription spécifique
    // ────────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $inscription = InscriptionFormation::with([
            'formation.artisan.user',
            'formation.etapes',
            'formation.ressourcesPubliques',
            'apprenant',
        ])->findOrFail($id);

        // Vérifier accès : propriétaire ou admin
        if ($inscription->apprenant_id !== $request->user()->id
            && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->inscriptionResource($inscription, true),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/inscrire
    // OCL : Un apprenant ne peut avoir qu'UNE formation en_cours
    // ────────────────────────────────────────────────────────────
    public function inscrire(Request $request, int $formationId): JsonResponse
    {
        $formation = Formation::with(['artisan.user'])->findOrFail($formationId);
        $user      = $request->user();

        // ── Vérifications ─────────────────────────────────────

        // 1. Formation active
        if (!$formation->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cette formation n\'est plus disponible.',
            ], 422);
        }

        // 2. Formation pas encore passée
        if ($formation->date_fin < now()->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette formation est déjà terminée.',
            ], 422);
        }

        // 3. Places disponibles
        if ($formation->estComplete()) {
            return response()->json([
                'success'           => false,
                'message'           => 'Cette formation est complète. Aucune place disponible.',
                'places_disponibles'=> 0,
            ], 422);
        }

        // 4. OCL : 1 seule formation active à la fois
        $formationActive = InscriptionFormation::where('apprenant_id', $user->id)
            ->where('statut_inscription', 'en_cours')
            ->with('formation')
            ->first();

        if ($formationActive) {
            return response()->json([
                'success'          => false,
                'message'          => 'Vous êtes déjà inscrit à une formation en cours. '
                                    . 'Terminez ou abandonnez-la avant de vous inscrire à une autre.',
                'code'             => 'OCL_FORMATION_ACTIVE',
                'formation_active' => [
                    'id'    => $formationActive->formation->id,
                    'titre' => $formationActive->formation->titre,
                    'lieu'  => $formationActive->formation->lieu,
                    'progression' => $formationActive->progression . '%',
                ],
            ], 422);
        }

        // 5. Déjà inscrit à cette même formation
        $dejaInscrit = InscriptionFormation::where([
            'formation_id' => $formationId,
            'apprenant_id' => $user->id,
        ])->first();

        if ($dejaInscrit) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à cette formation (statut : '
                           . $dejaInscrit->statut_inscription . ').',
                'inscription_id' => $dejaInscrit->id,
            ], 422);
        }

        // ── Créer l'inscription ────────────────────────────────
        $inscription = InscriptionFormation::create([
            'formation_id'       => $formationId,
            'apprenant_id'       => $user->id,
            'statut_inscription' => 'en_cours',
            'progression'        => 0,
            'date_inscription'   => now(),
            'date_debut_reelle'  => $formation->date_debut,
        ]);

        // ── Notifications ──────────────────────────────────────

        // Notifier l'apprenant
        Notification::envoyer(
            $user->id,
            'inscription_formation',
            '🎓 Inscription confirmée',
            "Vous êtes inscrit à « {$formation->titre} » qui commence le "
            . $formation->date_debut->format('d/m/Y') . " à {$formation->lieu}.",
            [
                'formation_id'   => $formationId,
                'inscription_id' => $inscription->id,
            ]
        );

        // Notifier l'artisan formateur
        Notification::envoyer(
            $formation->artisan->user_id,
            'nouvel_inscrit',
            '👤 Nouvel inscrit',
            "{$user->nom_complet} s'est inscrit à votre formation « {$formation->titre} ».",
            [
                'formation_id'   => $formationId,
                'inscription_id' => $inscription->id,
                'apprenant_id'   => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Inscription confirmée pour « {$formation->titre} » !",
            'data'    => [
                'inscription_id'    => $inscription->id,
                'formation_id'      => $formation->id,
                'formation_titre'   => $formation->titre,
                'formation_lieu'    => $formation->lieu,
                'date_debut'        => $formation->date_debut->format('d/m/Y'),
                'date_fin'          => $formation->date_fin->format('d/m/Y'),
                'artisan'           => $formation->artisan->user->nom_complet,
                'places_restantes'  => $formation->placesDisponibles(),
                'statut'            => 'en_cours',
                'progression'       => 0,
            ],
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/inscriptions/{id}/progression
    // Mettre à jour la progression (artisan ou admin)
    // OCL : 100% → terminée automatiquement
    // ────────────────────────────────────────────────────────────
    public function updateProgression(Request $request, int $id): JsonResponse
    {
        $inscription = InscriptionFormation::with('formation')->findOrFail($id);

        $validated = $request->validate([
            'progression' => 'required|integer|min:0|max:100',
        ]);

        // Vérifier que la formation n'est pas déjà terminée/abandonnée
        if (!$inscription->estEnCours()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette inscription est déjà '
                           . $inscription->statut_inscription . '.',
            ], 422);
        }

        $ancienneProgression = $inscription->progression;
        $inscription->mettreAJourProgression($validated['progression']);

        // Notification si étape importante
        $message = $inscription->estTerminee()
            ? '🎓 Félicitations ! Formation terminée avec succès !'
            : "Progression mise à jour : {$inscription->progression}%";

        if ($inscription->estTerminee()) {
            Notification::envoyer(
                $inscription->apprenant_id,
                'formation_terminee',
                '🎓 Formation terminée !',
                "Félicitations ! Vous avez terminé « {$inscription->formation->titre} ».",
                [
                    'formation_id'   => $inscription->formation_id,
                    'inscription_id' => $inscription->id,
                ]
            );
        }

        return response()->json([
            'success'              => true,
            'message'              => $message,
            'data'                 => [
                'inscription_id'      => $inscription->id,
                'ancienne_progression'=> $ancienneProgression,
                'progression'         => $inscription->progression,
                'statut_inscription'  => $inscription->statut_inscription,
                'est_terminee'        => $inscription->estTerminee(),
                'date_fin_reelle'     => $inscription->date_fin_reelle?->format('d/m/Y'),
                'certificat_url'      => $inscription->certificat_url,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/inscriptions/{id}/abandonner
    // L'apprenant abandonne la formation
    // ────────────────────────────────────────────────────────────
    public function abandonner(Request $request, int $id): JsonResponse
    {
        $inscription = InscriptionFormation::with('formation')->findOrFail($id);

        // Vérifier propriétaire
        if ($inscription->apprenant_id !== $request->user()->id
            && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        if (!$inscription->estEnCours()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette inscription n\'est pas en cours '
                           . '(statut actuel : ' . $inscription->statut_inscription . ').',
            ], 422);
        }

        $inscription->abandonner();

        return response()->json([
            'success' => true,
            'message' => 'Formation abandonnée. '
                       . 'Vous pouvez maintenant vous inscrire à une autre formation.',
            'data'    => [
                'inscription_id'     => $inscription->id,
                'formation_titre'    => $inscription->formation->titre,
                'statut_inscription' => 'abandonnee',
                'progression_finale' => $inscription->progression . '%',
                'date_fin_reelle'    => $inscription->date_fin_reelle?->format('d/m/Y'),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/inscriptions/{id}/suspendre
    // Admin uniquement — suspendre une inscription
    // ────────────────────────────────────────────────────────────
    public function suspendre(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé à l\'administrateur.',
            ], 403);
        }

        $inscription = InscriptionFormation::with(['formation','apprenant'])->findOrFail($id);

        if ($inscription->statut_inscription === 'terminee') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de suspendre une formation terminée.',
            ], 422);
        }

        $inscription->update(['statut_inscription' => 'suspendue']);

        Notification::envoyer(
            $inscription->apprenant_id,
            'inscription_suspendue',
            '⏸️ Inscription suspendue',
            "Votre inscription à « {$inscription->formation->titre} » a été suspendue "
            . "par l'administrateur. Contactez-nous pour plus d'informations.",
            ['inscription_id' => $inscription->id]
        );

        return response()->json([
            'success' => true,
            'message' => "Inscription de {$inscription->apprenant->nom_complet} suspendue.",
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/inscriptions/{id}/certificat
    // Admin — Délivrer un certificat
    // ────────────────────────────────────────────────────────────
    public function delivrerCertificat(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isAdmin() && !$request->user()->isArtisan()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        $inscription = InscriptionFormation::with(['formation','apprenant'])->findOrFail($id);

        if (!$inscription->estTerminee()) {
            return response()->json([
                'success' => false,
                'message' => 'Le certificat ne peut être délivré que pour une formation terminée.',
            ], 422);
        }

        $validated = $request->validate([
            'certificat_url' => 'required|string|max:500',
            'note_finale'    => 'nullable|numeric|min:0|max:20',
        ]);

        $inscription->update([
            'certificat_url' => $validated['certificat_url'],
            'note_finale'    => $validated['note_finale'] ?? null,
        ]);

        Notification::envoyer(
            $inscription->apprenant_id,
            'certificat_disponible',
            '🏅 Certificat disponible',
            "Votre certificat de la formation « {$inscription->formation->titre} » est disponible.",
            [
                'inscription_id' => $inscription->id,
                'certificat_url' => $validated['certificat_url'],
            ]
        );

        return response()->json([
            'success'       => true,
            'message'       => 'Certificat délivré avec succès.',
            'certificat_url'=> $validated['certificat_url'],
            'note_finale'   => $inscription->note_finale,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/inscriptions
    // Admin — Toutes les inscriptions
    // ────────────────────────────────────────────────────────────
    public function adminIndex(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

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
        ->when($request->filled('apprenant_id'),
            fn($q) => $q->where('apprenant_id', $request->apprenant_id)
        )
        ->latest('date_inscription')
        ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $inscriptions->map(fn($i) => $this->inscriptionResource($i)),
            'meta'    => [
                'total'        => $inscriptions->total(),
                'current_page' => $inscriptions->currentPage(),
                'last_page'    => $inscriptions->lastPage(),
            ],
            'stats' => [
                'total_en_cours'   => InscriptionFormation::where('statut_inscription','en_cours')->count(),
                'total_terminees'  => InscriptionFormation::where('statut_inscription','terminee')->count(),
                'total_abandonnees'=> InscriptionFormation::where('statut_inscription','abandonnee')->count(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/{id}/inscrits
    // Artisan ou Admin — Liste des inscrits à une formation
    // ────────────────────────────────────────────────────────────
    public function inscrits(Request $request, int $formationId): JsonResponse
    {
        $formation = Formation::findOrFail($formationId);

        // Vérifier accès artisan propriétaire ou admin
        if (!$request->user()->isAdmin()) {
            if (!$request->user()->artisan
                || $formation->artisan_id !== $request->user()->artisan->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Vous n\'êtes pas le formateur de cette formation.',
                ], 403);
            }
        }

        $inscrits = InscriptionFormation::with('apprenant')
            ->where('formation_id', $formationId)
            ->when($request->filled('statut'),
                fn($q) => $q->where('statut_inscription', $request->statut)
            )
            ->orderBy('date_inscription')
            ->get();

        return response()->json([
            'success'    => true,
            'formation'  => [
                'id'     => $formation->id,
                'titre'  => $formation->titre,
                'places_max'        => $formation->places_max,
                'places_disponibles'=> $formation->placesDisponibles(),
            ],
            'data'       => $inscrits->map(fn($i) => [
                'inscription_id'     => $i->id,
                'apprenant_id'       => $i->apprenant_id,
                'apprenant_nom'      => $i->apprenant?->nom_complet,
                'apprenant_email'    => $i->apprenant?->email,
                'apprenant_tel'      => $i->apprenant?->telephone,
                'statut_inscription' => $i->statut_inscription,
                'progression'        => $i->progression . '%',
                'note_finale'        => $i->note_finale,
                'date_inscription'   => $i->date_inscription?->format('d/m/Y'),
                'date_fin_reelle'    => $i->date_fin_reelle?->format('d/m/Y'),
                'a_certificat'       => !is_null($i->certificat_url),
            ]),
            'stats' => [
                'en_cours'   => $inscrits->where('statut_inscription','en_cours')->count(),
                'terminees'  => $inscrits->where('statut_inscription','terminee')->count(),
                'abandonnees'=> $inscrits->where('statut_inscription','abandonnee')->count(),
                'progression_moyenne' => round($inscrits->where('statut_inscription','en_cours')->avg('progression') ?? 0, 1).'%',
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // ── Resource Helper ──────────────────────────────────────────
    // ────────────────────────────────────────────────────────────
    private function inscriptionResource(InscriptionFormation $i, bool $detail = false): array
    {
        $data = [
            'id'                 => $i->id,
            'statut_inscription' => $i->statut_inscription,
            'statut_label'       => match($i->statut_inscription) {
                'en_cours'   => '📚 En cours',
                'terminee'   => '🎓 Terminée',
                'abandonnee' => '🚪 Abandonnée',
                'suspendue'  => '⏸️ Suspendue',
                default      => $i->statut_inscription,
            },
            'progression'        => $i->progression,
            'progression_label'  => $i->progression . '%',
            'note_finale'        => $i->note_finale
                                        ? (float) $i->note_finale
                                        : null,
            'note_label'         => $i->note_finale
                                        ? $i->note_finale . '/20'
                                        : null,
            'date_inscription'   => $i->date_inscription?->format('d/m/Y H:i'),
            'date_debut_reelle'  => $i->date_debut_reelle?->format('d/m/Y'),
            'date_fin_reelle'    => $i->date_fin_reelle?->format('d/m/Y'),
            'certificat_url'     => $i->certificat_url,
            'a_certificat'       => !is_null($i->certificat_url),
            'est_en_cours'       => $i->estEnCours(),
            'est_terminee'       => $i->estTerminee(),
        ];

        if ($i->relationLoaded('formation')) {
            $data['formation'] = [
                'id'              => $i->formation->id,
                'titre'           => $i->formation->titre,
                'lieu'            => $i->formation->lieu,
                'prix'            => $i->formation->prix,
                'date_debut'      => $i->formation->date_debut?->format('d/m/Y'),
                'date_fin'        => $i->formation->date_fin?->format('d/m/Y'),
                'image_url'       => $i->formation->image_url,
                'artisan'         => $i->formation->artisan?->user?->nom_complet,
                'places_restantes'=> $i->formation->placesDisponibles(),
            ];
        }

        if ($detail && $i->relationLoaded('formation')) {
            $data['etapes']    = $i->formation->relationLoaded('etapes')
                                    ? $i->formation->etapes
                                    : [];
            $data['ressources']= $i->formation->relationLoaded('ressourcesPubliques')
                                    ? $i->formation->ressourcesPubliques
                                    : [];
        }

        if ($i->relationLoaded('apprenant')) {
            $data['apprenant'] = [
                'id'        => $i->apprenant->id,
                'nom'       => $i->apprenant->nom_complet,
                'email'     => $i->apprenant->email,
                'telephone' => $i->apprenant->telephone,
            ];
        }

        return $data;
    }
}