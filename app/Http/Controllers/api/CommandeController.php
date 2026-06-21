<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\Livraison;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/commandes
    // Client — ses propres commandes
    // ────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $commandes = Commande::with(['lignes.produit', 'paiement', 'livraison'])
            ->where('client_id', $request->user()->id)
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $commandes->map(fn($c) => $this->commandeResource($c)),
            'meta'    => [
                'total'        => $commandes->total(),
                'current_page' => $commandes->currentPage(),
                'last_page'    => $commandes->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/commandes/{id}
    // ────────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $commande = Commande::with([
            'lignes.produit.artisan.user',
            'paiement',
            'livraison.historique',
            'client',
        ])->findOrFail($id);

        // Accès : client propriétaire ou admin
        if ($commande->client_id !== $request->user()->id
            && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->commandeResource($commande, true),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/commandes
    // Créer une commande avec vérification de stock
    // ────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'adresse_livraison' => 'required|string',
            'ville'             => 'required|string|max:100',
            'code_postal'       => 'nullable|string|max:10',
            'notes'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.produit_id'=> 'required|exists:produits,id',
            'items.*.quantite'  => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalHt = 0;
            $lignes  = [];

            foreach ($validated['items'] as $item) {
                // Verrouiller le produit pour éviter les conflits de stock
                $produit = Produit::lockForUpdate()->findOrFail($item['produit_id']);

                if (!$produit->is_active) {
                    throw new \Exception("Le produit « {$produit->nom} » n'est plus disponible.");
                }

                if ($produit->stock < $item['quantite']) {
                    throw new \Exception(
                        "Stock insuffisant pour « {$produit->nom} ». "
                        . "Disponible : {$produit->stock}, demandé : {$item['quantite']}."
                    );
                }

                $sousTotal = round($produit->prix * $item['quantite'], 2);
                $totalHt  += $sousTotal;

                $lignes[] = [
                    'produit_id'    => $produit->id,
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'remise'        => 0,
                    'sous_total'    => $sousTotal,
                ];

                // Décrémenter le stock
                $produit->decrement('stock', $item['quantite']);
            }

            $tva      = 0.20;
            $totalTtc = round($totalHt * (1 + $tva), 2);

            // Créer la commande
            $commande = Commande::create([
                'client_id'         => $request->user()->id,
                'statut'            => 'pending',
                'adresse_livraison' => $validated['adresse_livraison'],
                'ville'             => $validated['ville'],
                'code_postal'       => $validated['code_postal'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'total_ht'          => $totalHt,
                'tva'               => $tva,
                'total_ttc'         => $totalTtc,
            ]);

            // Créer les lignes
            $commande->lignes()->createMany($lignes);

            Livraison::creerPourCommande($commande);

            DB::commit();

            // Notification au client
            Notification::envoyer(
                $request->user()->id,
                'commande_creee',
                'Commande confirmée',
                "Votre commande #{$commande->id} a été enregistrée avec succès.",
                ['commande_id' => $commande->id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès.',
                'data'    => $this->commandeResource($commande->load('lignes.produit')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/commandes/{id}/annuler
    // Client — annuler une commande pending ou confirmed
    // ────────────────────────────────────────────────────────────
    public function annuler(Request $request, int $id): JsonResponse
    {
        $commande = Commande::with('lignes.produit')->findOrFail($id);

        if ($commande->client_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if (!in_array($commande->statut, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être annulée (statut : ' . $commande->statut . ').',
            ], 422);
        }

        DB::transaction(function () use ($commande) {
            // Remettre les stocks
            foreach ($commande->lignes as $ligne) {
                $ligne->produit->increment('stock', $ligne->quantite);
            }
            $commande->update(['statut' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée. Les stocks ont été rétablis.',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/commandes/{id}/statut
    // Admin ou artisan — changer le statut
    // ────────────────────────────────────────────────────────────
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $commande = Commande::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
        ]);

        // OCL : commande annulée immuable
        if ($commande->statut === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'OCL : Une commande annulée ne peut plus changer de statut.',
            ], 422);
        }

        $ancienStatut = $commande->statut;
        $commande->update(['statut' => $validated['statut']]);

        // Notification au client
        Notification::envoyer(
            $commande->client_id,
            'commande_statut',
            'Statut de commande mis à jour',
            "Votre commande #{$commande->id} est maintenant : {$validated['statut']}.",
            ['commande_id' => $commande->id, 'statut' => $validated['statut']]
        );

        return response()->json([
            'success'       => true,
            'message'       => 'Statut mis à jour.',
            'ancien_statut' => $ancienStatut,
            'nouveau_statut'=> $validated['statut'],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/commandes
    // Admin — toutes les commandes
    // ────────────────────────────────────────────────────────────
    public function adminIndex(Request $request): JsonResponse
    {
        $commandes = Commande::with(['client', 'paiement', 'livraison'])
            ->when($request->filled('statut'),    fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('client_id'), fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->filled('date_debut'),fn($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->filled('date_fin'),  fn($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $commandes->map(fn($c) => $this->commandeResource($c)),
            'meta'    => [
                'total'        => $commandes->total(),
                'current_page' => $commandes->currentPage(),
                'last_page'    => $commandes->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/admin/commandes/{id}
    // Admin — détail complet
    // ────────────────────────────────────────────────────────────
    public function adminShow(int $id): JsonResponse
    {
        $commande = Commande::with([
            'client',
            'lignes.produit.artisan.user',
            'paiement',
            'livraison.historique.modifiePar',
            'livraison.livreur',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->commandeResource($commande, true),
        ]);
    }

    // ── Resource helper ──────────────────────────────────────────
    private function commandeResource(Commande $c, bool $detail = false): array
    {
        $data = [
            'id'                => $c->id,
            'statut'            => $c->statut,
            'total_ht'          => $c->total_ht,
            'tva'               => $c->tva,
            'total_ttc'         => $c->total_ttc,
            'adresse_livraison' => $c->adresse_livraison,
            'ville'             => $c->ville,
            'notes'             => $c->notes,
            'created_at'        => $c->created_at?->format('d/m/Y H:i'),
            'client'            => $c->client ? [
                'id'  => $c->client->id,
                'nom' => $c->client->nom_complet,
                'tel' => $c->client->telephone,
            ] : null,
            'paiement' => $c->paiement ? [
                'statut'    => $c->paiement->statut,
                'methode'   => $c->paiement->methode,
                'montant'   => $c->paiement->montant,
                'reference' => $c->paiement->reference,
                'paid_at'   => $c->paiement->paid_at?->format('d/m/Y H:i'),
            ] : null,
            'livraison' => $c->livraison ? [
                'statut'                => $c->livraison->statut,
                'numero_suivi'          => $c->livraison->numero_suivi,
                'transporteur'          => $c->livraison->transporteur,
                'date_livraison_prevue' => $c->livraison->date_livraison_prevue?->format('d/m/Y'),
                'date_livraison_reelle' => $c->livraison->date_livraison_reelle?->format('d/m/Y H:i'),
            ] : null,
        ];

        if ($detail && $c->relationLoaded('lignes')) {
            $data['lignes'] = $c->lignes->map(fn($l) => [
                'produit_id'    => $l->produit_id,
                'produit'       => $l->produit?->nom,
                'image'         => isset($l->produit->images[0])
                                    ? asset("storage/{$l->produit->images[0]}")
                                    : null,
                'artisan'       => $l->produit?->artisan?->user?->nom_complet,
                'quantite'      => $l->quantite,
                'prix_unitaire' => $l->prix_unitaire,
                'remise'        => $l->remise,
                'sous_total'    => $l->sous_total,
            ]);
        }

        return $data;
    }
}