<?php

namespace App\Http\Controllers\Web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminCommandeController extends Controller
{
    public function index(Request $request)
    {
        $commandes = Commande::with(['client', 'paiement', 'livraison'])
            ->when($request->filled('statut'),     fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('date_debut'), fn($q) => $q->whereDate('created_at', '>=', $request->date_debut))
            ->when($request->filled('date_fin'),   fn($q) => $q->whereDate('created_at', '<=', $request->date_fin))
            ->latest()
            ->paginate(20);
 
        return view('admin.commandes', compact('commandes'));
    }
 
    public function show(int $id)
    {
        $commande = Commande::with([
            'client',
            'lignes.produit.artisan.user',
            'paiement',
            'livraison.historique.modifiePar',
            'livraison.livreur',
        ])->findOrFail($id);
 
        return view('admin.commande_show', compact('commande'));
    }
 
    public function updateStatut(Request $request, int $id)
    {
        $commande = Commande::findOrFail($id);
 
        $validated = $request->validate([
            'statut' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
        ]);
 
        // OCL : commande annulée immuable
        if ($commande->statut === 'cancelled') {
            return back()->with('error', 'OCL : Une commande annulée ne peut plus changer de statut.');
        }
 
        $commande->update(['statut' => $validated['statut']]);
 
        // Notifier le client
        Notification::envoyer(
            $commande->client_id,
            'commande_statut',
            'Statut de commande mis à jour',
            "Votre commande #{$commande->id} est maintenant : {$validated['statut']}.",
            ['commande_id' => $commande->id]
        );
 
        return back()->with('success', "Statut mis à jour → {$validated['statut']}");
    }
}
 