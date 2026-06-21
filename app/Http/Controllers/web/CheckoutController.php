<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Livraison;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $panier = session('panier', []);

        if (empty($panier)) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide.');
        }

        return view('commandes.checkout');
    }

    public function store(Request $request)
    {
        $panier = session('panier', []);

        if (empty($panier)) {
            return redirect()->route('catalogue.index')
                ->with('error', 'Votre panier est vide.');
        }

        $validated = $request->validate([
            'adresse_livraison' => 'required|string|max:300',
            'ville'             => 'required|string|max:100',
            'code_postal'       => 'nullable|string|max:10',
            'notes'             => 'nullable|string|max:500',
            'methode'           => 'required|in:livraison,carte,virement',
        ]);

        DB::beginTransaction();

        try {
            $totalHt = 0;
            $lignes  = [];

            foreach ($panier as $produitId => $item) {
                $produit = Produit::lockForUpdate()->find($produitId);

                if (! $produit || ! $produit->is_active) {
                    throw new \Exception("Le produit « {$item['nom']} » n'est plus disponible.");
                }

                if ($produit->stock < $item['quantite']) {
                    throw new \Exception(
                        "Stock insuffisant pour « {$produit->nom} ». "
                        ."Disponible : {$produit->stock}."
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
            }

            $tva      = 0.20;
            $totalTtc = round($totalHt * (1 + $tva), 2);

            $commande = Commande::create([
                'client_id'         => auth()->id(),
                'statut'            => 'pending',
                'adresse_livraison' => $validated['adresse_livraison'],
                'ville'             => $validated['ville'],
                'code_postal'       => $validated['code_postal'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'total_ht'          => $totalHt,
                'tva'               => $tva,
                'total_ttc'         => $totalTtc,
            ]);

            $commande->lignes()->createMany($lignes);

            Paiement::create([
                'commande_id' => $commande->id,
                'methode'     => $validated['methode'],
                'statut'      => 'pending',
                'montant'     => $commande->total_ttc,
                'reference'   => 'PAY-'.strtoupper(Str::random(10)),
            ]);

            if ($validated['methode'] === 'livraison') {
                $commande->update(['statut' => 'confirmed']);
            }

            // Toujours créer une livraison — quel que soit le rôle acheteur ou le mode de paiement
            Livraison::creerPourCommande($commande);

            session()->forget('panier');
            session()->forget('panier_count');

            Notification::envoyer(
                auth()->id(),
                'commande_creee',
                '✅ Commande confirmée',
                "Votre commande #{$commande->id} a été enregistrée.",
                ['commande_id' => $commande->id]
            );

            DB::commit();

            return redirect()->route('commandes.confirmation', $commande->id)
                ->with('success', 'Commande passée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
