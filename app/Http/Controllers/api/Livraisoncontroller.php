<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Livraison;
use App\Models\LivraisonHistorique;
use App\Models\Commande;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LivraisonController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/livraisons/{id}
    // Client ou livreur ou admin
    // ────────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $livraison = Livraison::with([
            'commande.client',
            'livreur',
            'historique.modifiePar',
        ])->findOrFail($id);

        $user = $request->user();

        // Accès : client propriétaire, livreur assigné, ou admin
        $acces = $user->isAdmin()
            || $livraison->commande->client_id === $user->id
            || $livraison->livreur_id === $user->id;

        if (!$acces) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->livraisonResource($livraison, true),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/livraisons/{id}/historique
    // Tracking complet de la livraison
    // ────────────────────────────────────────────────────────────
    public function historique(int $id): JsonResponse
    {
        $livraison = Livraison::findOrFail($id);

        $historique = LivraisonHistorique::with('modifiePar')
            ->where('livraison_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'   => true,
            'livraison' => [
                'id'           => $livraison->id,
                'statut'       => $livraison->statut,
                'numero_suivi' => $livraison->numero_suivi,
                'transporteur' => $livraison->transporteur,
            ],
            'historique' => $historique->map(fn($h) => [
                'statut'       => $h->statut,
                'commentaire'  => $h->commentaire,
                'localisation' => $h->localisation,
                'par'          => $h->modifiePar?->nom_complet ?? 'Système',
                'date'         => $h->created_at?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/livraisons/mes-livraisons
    // Livreur — ses livraisons assignées
    // ────────────────────────────────────────────────────────────
    public function mesLivraisons(Request $request): JsonResponse
    {
        $livraisons = Livraison::with(['commande.client'])
            ->where('livreur_id', $request->user()->id)
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $livraisons->map(fn($l) => $this->livraisonResource($l)),
            'meta'    => [
                'total'        => $livraisons->total(),
                'current_page' => $livraisons->currentPage(),
                'last_page'    => $livraisons->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/livraisons/{id}/statut
    // Livreur ou admin — mettre à jour le statut
    // ────────────────────────────────────────────────────────────
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $livraison = Livraison::with('commande')->findOrFail($id);

        $validated = $request->validate([
            'statut'       => 'required|in:assigned,in_transit,delivered,failed',
            'commentaire'  => 'nullable|string|max:500',
            'localisation' => 'nullable|string|max:200',
        ]);

        // OCL : livraison livrée immuable
        if ($livraison->estLivree()) {
            return response()->json([
                'success' => false,
                'message' => 'OCL : Une livraison déjà livrée ne peut plus changer de statut.',
                'code'    => 'OCL_LIVRAISON_LIVREE',
            ], 422);
        }

        // Vérifier accès livreur
        if ($request->user()->isLivreur() && $livraison->livreur_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Cette livraison ne vous est pas assignée.'], 403);
        }

        $ancienStatut = $livraison->statut;

        // Mettre à jour via le helper du model (gère historique + sync commande)
        $livraison->changerStatut(
            $validated['statut'],
            $request->user()->id,
            $validated['commentaire'] ?? null
        );

        // Notification au client
        Notification::envoyer(
            $livraison->commande->client_id,
            'livraison_statut',
            'Mise à jour de votre livraison',
            "Votre livraison est maintenant : {$validated['statut']}.",
            [
                'livraison_id'  => $livraison->id,
                'commande_id'   => $livraison->commande_id,
                'statut'        => $validated['statut'],
                'numero_suivi'  => $livraison->numero_suivi,
            ]
        );

        return response()->json([
            'success'        => true,
            'message'        => 'Statut de livraison mis à jour.',
            'ancien_statut'  => $ancienStatut,
            'nouveau_statut' => $validated['statut'],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/livraisons/{id}/confirmer
    // Livreur — confirmer la livraison au destinataire
    // ────────────────────────────────────────────────────────────
    public function confirmer(Request $request, int $id): JsonResponse
    {
        $livraison = Livraison::with('commande')->findOrFail($id);

        if ($livraison->livreur_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($livraison->estLivree()) {
            return response()->json(['success' => false, 'message' => 'Déjà confirmée comme livrée.'], 422);
        }

        $livraison->changerStatut(Livraison::STATUT_LIVREE, $request->user()->id, 'Livraison confirmée par le livreur.');

        return response()->json([
            'success' => true,
            'message' => '✅ Livraison confirmée avec succès.',
            'data'    => [
                'livraison_id'         => $livraison->id,
                'statut'               => Livraison::STATUT_LIVREE,
                'date_livraison_reelle'=> $livraison->fresh()->date_livraison_reelle?->format('d/m/Y H:i'),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/livraisons/{id}/preuve
    // Livreur — uploader une preuve de livraison (photo)
    // ────────────────────────────────────────────────────────────
    public function uploadPreuve(Request $request, int $id): JsonResponse
    {
        $livraison = Livraison::findOrFail($id);

        if ($livraison->livreur_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'preuve' => 'required|image|max:5120',
        ]);

        $path = $request->file('preuve')->store("livraisons/{$id}/preuves", 'public');
        $livraison->update(['preuve_livraison_url' => $path]);

        return response()->json([
            'success'    => true,
            'message'    => 'Preuve de livraison enregistrée.',
            'preuve_url' => asset("storage/{$path}"),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/admin/commandes/{id}/livraison  (via CommandeController)
    // POST /api/admin/livraisons/{id}/assigner
    // Admin — assigner un livreur
    // ────────────────────────────────────────────────────────────
    public function assigner(Request $request, int $id): JsonResponse
    {
        $livraison = Livraison::findOrFail($id);

        $validated = $request->validate([
            'livreur_id'            => 'required|exists:users,id',
            'transporteur'          => 'nullable|string|max:100',
            'numero_suivi'          => 'nullable|string|max:100',
            'date_livraison_prevue' => 'nullable|date|after_or_equal:today',
            'frais_livraison'       => 'nullable|numeric|min:0',
        ]);

        // Vérifier que l'user est bien un livreur
        $livreur = \App\Models\User::findOrFail($validated['livreur_id']);
        if ($livreur->role !== 'livreur') {
            return response()->json([
                'success' => false,
                'message' => 'L\'utilisateur sélectionné n\'est pas un livreur.',
            ], 422);
        }

        $livraison->update([
            'livreur_id'            => $validated['livreur_id'],
            'transporteur'          => $validated['transporteur'] ?? $livraison->transporteur,
            'numero_suivi'          => $validated['numero_suivi'] ?? $livraison->numero_suivi,
            'date_livraison_prevue' => $validated['date_livraison_prevue'] ?? $livraison->date_livraison_prevue,
            'frais_livraison'       => $validated['frais_livraison'] ?? $livraison->frais_livraison,
        ]);

        // Enregistrer dans l'historique
        LivraisonHistorique::create([
            'livraison_id' => $livraison->id,
            'statut'       => $livraison->statut,
            'commentaire'  => "Livreur assigné : {$livreur->nom_complet}",
            'changed_by'   => $request->user()->id,
        ]);

        // Notifier le livreur
        Notification::envoyer(
            $livreur->id,
            'livraison_assignee',
            'Nouvelle livraison assignée',
            "Une livraison vous a été assignée (commande #{$livraison->commande_id}).",
            ['livraison_id' => $livraison->id]
        );

        return response()->json([
            'success' => true,
            'message' => "Livraison assignée à {$livreur->nom_complet}.",
            'data'    => $this->livraisonResource($livraison->fresh()),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/livraisons
    // Admin — toutes les livraisons
    // ────────────────────────────────────────────────────────────
    public function adminIndex(Request $request): JsonResponse
    {
        $livraisons = Livraison::with(['commande.client', 'livreur'])
            ->when($request->filled('statut'),    fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('livreur_id'),fn($q) => $q->where('livreur_id', $request->livreur_id))
            ->when(!$request->filled('livreur_id') && $request->filled('sans_livreur'),
                   fn($q) => $q->whereNull('livreur_id'))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $livraisons->map(fn($l) => $this->livraisonResource($l)),
            'meta'    => [
                'total'        => $livraisons->total(),
                'current_page' => $livraisons->currentPage(),
                'last_page'    => $livraisons->lastPage(),
            ],
        ]);
    }

    // ── Resource helper ──────────────────────────────────────────
    private function livraisonResource(Livraison $l, bool $detail = false): array
    {
        $data = [
            'id'                    => $l->id,
            'commande_id'           => $l->commande_id,
            'statut'                => $l->statut,
            'numero_suivi'          => $l->numero_suivi,
            'transporteur'          => $l->transporteur,
            'adresse_livraison'     => $l->adresse_livraison,
            'ville'                 => $l->ville,
            'region'                => $l->region,
            'telephone_recepteur'   => $l->telephone_recepteur,
            'frais_livraison'       => $l->frais_livraison,
            'date_expedition'       => $l->date_expedition?->format('d/m/Y H:i'),
            'date_livraison_prevue' => $l->date_livraison_prevue?->format('d/m/Y'),
            'date_livraison_reelle' => $l->date_livraison_reelle?->format('d/m/Y H:i'),
            'preuve_livraison_url'  => $l->preuve_livraison_url
                                        ? asset("storage/{$l->preuve_livraison_url}")
                                        : null,
            'livreur' => $l->livreur ? [
                'id'  => $l->livreur->id,
                'nom' => $l->livreur->nom_complet,
                'tel' => $l->livreur->telephone,
            ] : null,
            'client' => $l->relationLoaded('commande') && $l->commande->relationLoaded('client')
                ? [
                    'nom' => $l->commande->client->nom_complet,
                    'tel' => $l->commande->client->telephone,
                ]
                : null,
        ];

        if ($detail && $l->relationLoaded('historique')) {
            $data['historique'] = $l->historique->map(fn($h) => [
                'statut'       => $h->statut,
                'commentaire'  => $h->commentaire,
                'localisation' => $h->localisation,
                'par'          => $h->modifiePar?->nom_complet ?? 'Système',
                'date'         => $h->created_at?->format('d/m/Y H:i'),
            ]);
        }

        return $data;
    }
}