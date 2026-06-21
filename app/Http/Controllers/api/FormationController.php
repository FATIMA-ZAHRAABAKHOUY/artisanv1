<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\MateriauFormation;
use App\Models\OutilFormation;
use App\Models\EtapeFormation;
use App\Models\RessourceFormation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FormationController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/formations
    // Public — liste avec filtres
    // ────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Formation::with(['artisan.user', 'formateurs.user'])
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

        $formations = $query->orderBy('date_debut')->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data'    => $formations->map(fn($f) => $this->formationResource($f)),
            'meta'    => [
                'total'        => $formations->total(),
                'current_page' => $formations->currentPage(),
                'last_page'    => $formations->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/{id}
    // Public — détail complet
    // ────────────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $formation = Formation::with([
            'artisan.user',
            'formateurs.user',
            'etapes',
            'materiaux.fournisseurs.fournisseur',
            'outils.fournisseurs.fournisseur',
            'ressourcesPubliques',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formationResource($formation, true),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations
    // Artisan uniquement
    // ────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titre'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'date_debut'  => 'required|date|after_or_equal:today',
            'date_fin'    => 'required|date|after:date_debut',
            'prix'        => 'required|numeric|min:0',
            'places_max'  => 'required|integer|min:1|max:50',
            'lieu'        => 'nullable|string|max:200',
            'image'       => 'nullable|image|max:3072',
        ]);

        $artisan = $request->user()->artisan;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store("formations/{$artisan->id}", 'public');
        }

        $formation = Formation::create([
            ...$validated,
            'artisan_id' => $artisan->id,
            'image'      => $imagePath,
            'is_active'  => true,
        ]);

        // Ajouter l'artisan comme formateur principal
        if ($artisan->formateur) {
            $formation->formateurs()->attach($artisan->formateur->id, ['role' => 'principal']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Formation créée avec succès.',
            'data'    => $this->formationResource($formation->load('artisan.user')),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/{id}
    // ────────────────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'titre'      => 'sometimes|string|max:200',
            'description'=> 'nullable|string',
            'date_debut' => 'sometimes|date',
            'date_fin'   => 'sometimes|date|after:date_debut',
            'prix'       => 'sometimes|numeric|min:0',
            'places_max' => 'sometimes|integer|min:1',
            'lieu'       => 'nullable|string|max:200',
            'is_active'  => 'sometimes|boolean',
        ]);

        $formation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Formation mise à jour.',
            'data'    => $this->formationResource($formation->fresh()),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // DELETE /api/formations/{id}
    // ────────────────────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $formation->update(['is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Formation désactivée.']);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/inscrire
    // OCL : 1 seule formation active à la fois
    // ────────────────────────────────────────────────────────────
    public function inscrire(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);
        $user      = $request->user();

        // Places disponibles
        if ($formation->estComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette formation est complète.',
            ], 422);
        }

        // OCL : une seule formation en_cours
        $active = InscriptionFormation::where('apprenant_id', $user->id)
                    ->where('statut_inscription', 'en_cours')
                    ->with('formation')
                    ->first();

        if ($active) {
            return response()->json([
                'success'          => false,
                'message'          => 'Vous êtes déjà inscrit à une formation en cours. '
                                    . 'Terminez-la avant de vous inscrire à une autre.',
                'formation_active' => $active->formation->titre,
                'code'             => 'OCL_FORMATION_ACTIVE',
            ], 422);
        }

        // Déjà inscrit ?
        if (InscriptionFormation::where(['formation_id' => $id, 'apprenant_id' => $user->id])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à cette formation.',
            ], 422);
        }

        $inscription = InscriptionFormation::create([
            'formation_id'       => $id,
            'apprenant_id'       => $user->id,
            'statut_inscription' => 'en_cours',
            'progression'        => 0,
            'date_debut_reelle'  => $formation->date_debut,
        ]);

        // Notification
        Notification::envoyer(
            $user->id,
            'inscription_formation',
            'Inscription confirmée',
            "Vous êtes inscrit à « {$formation->titre} » qui commence le {$formation->date_debut->format('d/m/Y')}.",
            ['formation_id' => $id, 'inscription_id' => $inscription->id]
        );

        return response()->json([
            'success' => true,
            'message' => "Inscription confirmée pour « {$formation->titre} ».",
            'data'    => [
                'inscription_id'    => $inscription->id,
                'formation'         => $formation->titre,
                'date_debut'        => $formation->date_debut->format('d/m/Y'),
                'lieu'              => $formation->lieu,
                'places_restantes'  => $formation->placesDisponibles(),
            ],
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/inscriptions/{id}/progression
    // ────────────────────────────────────────────────────────────
    public function updateProgression(Request $request, int $inscriptionId): JsonResponse
    {
        $inscription = InscriptionFormation::with('formation')->findOrFail($inscriptionId);

        $validated = $request->validate([
            'progression' => 'required|integer|min:0|max:100',
        ]);

        $inscription->mettreAJourProgression($validated['progression']);

        $message = $inscription->estTerminee()
            ? '🎓 Formation terminée ! Félicitations.'
            : "Progression : {$inscription->progression}%";

        // Notification si terminée
        if ($inscription->estTerminee()) {
            Notification::envoyer(
                $inscription->apprenant_id,
                'formation_terminee',
                'Formation terminée',
                "Félicitations ! Vous avez terminé « {$inscription->formation->titre} ».",
                ['formation_id' => $inscription->formation_id]
            );
        }

        return response()->json([
            'success'     => true,
            'message'     => $message,
            'progression' => $inscription->progression,
            'statut'      => $inscription->statut_inscription,
            'terminee'    => $inscription->estTerminee(),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/formations/inscriptions/{id}/abandonner
    // ────────────────────────────────────────────────────────────
    public function abandonner(Request $request, int $inscriptionId): JsonResponse
    {
        $inscription = InscriptionFormation::findOrFail($inscriptionId);

        if ($inscription->apprenant_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $inscription->abandonner();

        return response()->json([
            'success' => true,
            'message' => 'Formation abandonnée. Vous pouvez maintenant vous inscrire à une autre.',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/mes-inscriptions
    // ────────────────────────────────────────────────────────────
    public function mesInscriptions(Request $request): JsonResponse
    {
        $inscriptions = InscriptionFormation::with(['formation.artisan.user'])
            ->where('apprenant_id', $request->user()->id)
            ->latest('date_inscription')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $inscriptions->map(fn($i) => [
                'id'                 => $i->id,
                'statut_inscription' => $i->statut_inscription,
                'progression'        => $i->progression,
                'note_finale'        => $i->note_finale,
                'date_inscription'   => $i->date_inscription?->format('d/m/Y'),
                'date_fin_reelle'    => $i->date_fin_reelle?->format('d/m/Y'),
                'certificat_url'     => $i->certificat_url,
                'formation'          => [
                    'id'         => $i->formation->id,
                    'titre'      => $i->formation->titre,
                    'lieu'       => $i->formation->lieu,
                    'date_debut' => $i->formation->date_debut->format('d/m/Y'),
                    'date_fin'   => $i->formation->date_fin->format('d/m/Y'),
                    'artisan'    => $i->formation->artisan->user->nom_complet,
                ],
            ]),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/{id}/suggestions-achat
    // Matériaux + outils avec fournisseurs recommandés
    // ────────────────────────────────────────────────────────────
    public function suggestionsAchat(int $id): JsonResponse
    {
        $formation = Formation::with([
            'materiaux.fournisseurs.fournisseur',
            'outils.fournisseurs.fournisseur',
        ])->findOrFail($id);

        $materiaux = $formation->materiaux
            ->filter(fn($m) => !$m->est_fourni)  // seulement ce que l'apprenant doit apporter
            ->map(fn($m) => [
                'id'          => $m->id,
                'nom'         => $m->nom,
                'type'        => $m->type,
                'couleur'     => $m->couleur,
                'quantite'    => $m->quantite,
                'unite'       => $m->unite,
                'description' => $m->description,
                'fournisseurs'=> $m->fournisseurs->map(fn($fm) => [
                    'fournisseur_id'         => $fm->fournisseur->id,
                    'nom'                    => $fm->fournisseur->nom,
                    'type'                   => $fm->fournisseur->type,
                    'ville'                  => $fm->fournisseur->ville,
                    'telephone'              => $fm->fournisseur->telephone,
                    'whatsapp'               => $fm->fournisseur->whatsapp,
                    'site_web'               => $fm->fournisseur->site_web,
                    'nom_produit'            => $fm->nom_produit_fournisseur,
                    'reference'              => $fm->reference_produit,
                    'prix_unitaire'          => $fm->prix_unitaire,
                    'unite_prix'             => $fm->unite_prix,
                    'url_produit'            => $fm->url_produit,
                    'delai_min'              => $fm->delai_livraison_min,
                    'delai_max'              => $fm->delai_livraison_max,
                    'remise_cooperative'     => $fm->fournisseur->remise_cooperative,
                    'est_recommande'         => $fm->est_recommande,
                    'stock_disponible'       => $fm->stock_disponible,
                    'notes'                  => $fm->notes_apprenant,
                ]),
            ]);

        $outils = $formation->outils
            ->filter(fn($o) => !$o->est_fourni)
            ->map(fn($o) => [
                'id'          => $o->id,
                'nom'         => $o->nom,
                'quantite'    => $o->quantite,
                'description' => $o->description,
                'fournisseurs'=> $o->fournisseurs->map(fn($fo) => [
                    'fournisseur_id'     => $fo->fournisseur->id,
                    'nom'                => $fo->fournisseur->nom,
                    'type'               => $fo->fournisseur->type,
                    'ville'              => $fo->fournisseur->ville,
                    'telephone'          => $fo->fournisseur->telephone,
                    'site_web'           => $fo->fournisseur->site_web,
                    'nom_produit'        => $fo->nom_produit_fournisseur,
                    'prix_unitaire'      => $fo->prix_unitaire,
                    'unite_prix'         => $fo->unite_prix,
                    'url_produit'        => $fo->url_produit,
                    'delai_min'          => $fo->delai_livraison_min,
                    'delai_max'          => $fo->delai_livraison_max,
                    'remise_cooperative' => $fo->fournisseur->remise_cooperative,
                    'est_recommande'     => $fo->est_recommande,
                    'stock_disponible'   => $fo->stock_disponible,
                    'notes'              => $fo->notes_apprenant,
                ]),
            ]);

        return response()->json([
            'success'   => true,
            'formation' => $formation->titre,
            'message'   => 'Voici ce que vous devez apporter pour cette formation.',
            'data'      => [
                'materiaux' => $materiaux->values(),
                'outils'    => $outils->values(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/formations/{id}/ressources
    // Inscrit confirmé uniquement pour les ressources privées
    // ────────────────────────────────────────────────────────────
    public function ressources(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        // Vérifier inscription active
        $inscrit = InscriptionFormation::where([
            'formation_id' => $id,
            'apprenant_id' => $request->user()->id,
        ])->whereIn('statut_inscription', ['en_cours', 'terminee'])->exists();

        $query = $formation->ressources();

        if (!$inscrit && !$request->user()->isAdmin()) {
            $query->where('est_public', true);
        }

        $ressources = $query->orderBy('ordre')->get();

        return response()->json([
            'success' => true,
            'inscrit' => $inscrit,
            'data'    => $ressources->map(fn($r) => [
                'id'             => $r->id,
                'type'           => $r->type,
                'titre'          => $r->titre,
                'description'    => $r->description,
                'url'            => $r->url_complete,
                'est_public'     => $r->est_public,
                'duree_secondes' => $r->duree_secondes,
                'ordre'          => $r->ordre,
            ]),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/materiaux
    // ────────────────────────────────────────────────────────────
    public function ajouterMateriau(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'type'        => 'required|in:fil,laine,coton,soie,lin,raphia,corde,autre',
            'couleur'     => 'nullable|string|max:100',
            'quantite'    => 'required|numeric|min:0',
            'unite'       => 'required|in:metre,gramme,kilogramme,pelote,bobine,piece,autre',
            'description' => 'nullable|string',
            'est_fourni'  => 'boolean',
            'ordre'       => 'integer|min:0',
        ]);

        $materiau = $formation->materiaux()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Matériau ajouté.',
            'data'    => $materiau,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/outils
    // ────────────────────────────────────────────────────────────
    public function ajouterOutil(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string',
            'quantite'    => 'integer|min:1',
            'est_fourni'  => 'boolean',
            'lien_achat'  => 'nullable|url',
            'ordre'       => 'integer|min:0',
        ]);

        $outil = $formation->outils()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Outil ajouté.',
            'data'    => $outil,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/etapes
    // ────────────────────────────────────────────────────────────
    public function ajouterEtape(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'numero_ordre'     => 'required|integer|min:1',
            'titre'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'duree_minutes'    => 'nullable|integer|min:1',
            'objectif'         => 'nullable|string',
            'materiaux_requis' => 'nullable|string',
        ]);

        $etape = $formation->etapes()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Étape ajoutée.',
            'data'    => $etape,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/formations/{id}/ressources
    // ────────────────────────────────────────────────────────────
    public function ajouterRessource(Request $request, int $id): JsonResponse
    {
        $formation = Formation::findOrFail($id);

        if ($formation->artisan->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'type'       => 'required|in:video,document_pdf,image,lien_externe',
            'titre'      => 'required|string|max:200',
            'description'=> 'nullable|string',
            'est_public' => 'boolean',
            'ordre'      => 'integer|min:0',
            'fichier'    => 'nullable|file|max:102400', // 100MB max
            'url'        => 'required_without:fichier|nullable|url',
        ]);

        $urlFichier = $validated['url'] ?? null;
        if ($request->hasFile('fichier')) {
            $urlFichier = $request->file('fichier')->store("formations/{$id}/ressources", 'public');
        }

        $ressource = $formation->ressources()->create([
            ...$validated,
            'url' => $urlFichier,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ressource ajoutée.',
            'data'    => $ressource,
        ], 201);
    }

    // ── Resource helper ──────────────────────────────────────────
    private function formationResource(Formation $f, bool $detail = false): array
    {
        $data = [
            'id'                 => $f->id,
            'titre'              => $f->titre,
            'description'        => $f->description,
            'date_debut'         => $f->date_debut?->format('d/m/Y'),
            'date_fin'           => $f->date_fin?->format('d/m/Y'),
            'prix'               => $f->prix,
            'places_max'         => $f->places_max,
            'places_disponibles' => $f->placesDisponibles(),
            'est_complete'       => $f->estComplete(),
            'lieu'               => $f->lieu,
            'image_url'          => $f->image_url,
            'artisan'            => $f->artisan ? [
                'id'        => $f->artisan->id,
                'nom'       => $f->artisan->user->nom_complet,
                'specialite'=> $f->artisan->specialite,
            ] : null,
            'formateurs' => $f->relationLoaded('formateurs')
                ? $f->formateurs->map(fn($fm) => [
                    'nom'         => $fm->user->nom_complet,
                    'role'        => $fm->pivot->role,
                    'est_externe' => $fm->est_externe,
                    'specialite'  => $fm->specialite,
                ])
                : [],
        ];

        if ($detail) {
            $data['etapes']     = $f->relationLoaded('etapes')     ? $f->etapes     : [];
            $data['materiaux']  = $f->relationLoaded('materiaux')  ? $f->materiaux  : [];
            $data['outils']     = $f->relationLoaded('outils')     ? $f->outils     : [];
            $data['ressources'] = $f->relationLoaded('ressourcesPubliques') ? $f->ressourcesPubliques : [];
        }

        return $data;
    }
}
