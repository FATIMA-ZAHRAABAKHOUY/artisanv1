<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Commande;
use App\Models\Livraison;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    // POST /api/paiements/{commande_id}
    public function payer(Request $request, int $commandeId): JsonResponse
    {
        $commande = Commande::findOrFail($commandeId);

        if ($commande->client_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($commande->paiement && $commande->paiement->estPaye()) {
            return response()->json(['success' => false, 'message' => 'Cette commande est déjà payée.'], 422);
        }

        $validated = $request->validate([
            'methode' => 'required|in:carte,livraison,virement,cmi',
        ]);

        // Créer ou mettre à jour le paiement
        $paiement = Paiement::updateOrCreate(
            ['commande_id' => $commandeId],
            [
                'methode'   => $validated['methode'],
                'statut'    => $validated['methode'] === 'livraison' ? 'pending' : 'pending',
                'montant'   => $commande->total_ttc,
                'reference' => 'PAY-' . strtoupper(Str::random(10)),
            ]
        );

        // Paiement à la livraison → confirmer directement
        if ($validated['methode'] === 'livraison') {
            $paiement->update(['statut' => 'pending']); // sera confirmé à la livraison
            $commande->update(['statut' => 'confirmed']);

            // Créer livraison automatiquement
            Livraison::firstOrCreate(
                ['commande_id' => $commandeId],
                [
                    'adresse_livraison' => $commande->adresse_livraison,
                    'ville'             => $commande->ville,
                    'code_postal'       => $commande->code_postal,
                    'statut'            => Livraison::STATUT_ASSIGNEE,
                    'frais_livraison'   => 0,
                ]
            );

            Notification::envoyer(
                $commande->client_id,
                'paiement_cree',
                'Commande confirmée',
                "Votre commande #{$commande->id} est confirmée. Paiement à la livraison.",
                ['commande_id' => $commande->id, 'paiement_id' => $paiement->id]
            );
        }

        return response()->json([
            'success'   => true,
            'message'   => $validated['methode'] === 'livraison'
                ? 'Commande confirmée. Paiement à la livraison.'
                : 'Paiement initié. Référence : ' . $paiement->reference,
            'data'      => [
                'paiement_id' => $paiement->id,
                'reference'   => $paiement->reference,
                'montant'     => $paiement->montant,
                'methode'     => $paiement->methode,
                'statut'      => $paiement->statut,
            ],
        ]);
    }

    // GET /api/paiements/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $paiement = Paiement::with('commande.client')->findOrFail($id);

        if ($paiement->commande->client_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        return response()->json(['success' => true, 'data' => $paiement]);
    }

    // POST /api/paiements/webhook (sans auth)
    public function webhook(Request $request): JsonResponse
    {
        $reference = $request->input('reference');
        $statut    = $request->input('statut'); // paid | failed

        $paiement = Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            return response()->json(['success' => false, 'message' => 'Référence introuvable.'], 404);
        }

        if ($statut === 'paid') {
            $paiement->update(['statut' => 'paid', 'paid_at' => now()]);
            $paiement->commande->update(['statut' => 'confirmed']);

            Livraison::firstOrCreate(
                ['commande_id' => $paiement->commande_id],
                [
                    'adresse_livraison' => $paiement->commande->adresse_livraison,
                    'ville'             => $paiement->commande->ville,
                    'statut'            => Livraison::STATUT_ASSIGNEE,
                    'frais_livraison'   => 0,
                ]
            );

            Notification::envoyer(
                $paiement->commande->client_id,
                'paiement_confirme',
                '✅ Paiement confirmé',
                "Votre paiement de {$paiement->montant} MAD a été confirmé.",
                ['commande_id' => $paiement->commande_id]
            );
        } elseif ($statut === 'failed') {
            $paiement->update(['statut' => 'failed']);
        }

        return response()->json(['success' => true]);
    }
}
