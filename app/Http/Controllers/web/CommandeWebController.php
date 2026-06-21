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
// ================================================================
class CommandeWebController extends Controller
{
    // GET /commandes
    public function index(Request $request)
    {
        $commandes = Commande::with(['lignes.produit', 'paiement', 'livraison'])
            ->where('client_id', auth()->id())
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(10);
 
        return view('commandes.index', compact('commandes'));
    }
 
    // GET /commandes/{id}
    public function show(int $id)
    {
        $commande = Commande::with([
            'lignes.produit.artisan.user',
            'paiement',
            'livraison.historique.modifiePar',
            'livraison.livreur',
        ])->where('client_id', auth()->id())->findOrFail($id);
 
        return view('commandes.show', compact('commande'));
    }
 
    // GET /commandes/{id}/confirmation
    public function confirmation(int $id)
    {
        $commande = Commande::with('paiement')
            ->where('client_id', auth()->id())
            ->findOrFail($id);
 
        return view('commandes.confirmation', compact('commande'));
    }
 
    // POST /commandes/{id}/annuler
    public function annuler(int $id)
    {
        $commande = Commande::with('lignes.produit')
            ->where('client_id', auth()->id())
            ->findOrFail($id);
 
        if (!in_array($commande->statut, ['pending', 'confirmed'])) {
            return back()->with('error',
                'Cette commande ne peut plus être annulée (statut : '.$commande->statut.').');
        }
 
        DB::transaction(function () use ($commande) {
            foreach ($commande->lignes as $ligne) {
                $ligne->produit?->increment('stock', $ligne->quantite);
            }
            $commande->update(['statut' => 'cancelled']);
        });
 
        return back()->with('success', 'Commande annulée. Les stocks ont été rétablis.');
    }
}