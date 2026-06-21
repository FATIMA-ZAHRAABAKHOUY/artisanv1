<?php

namespace App\Http\Controllers\web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $livraisons = Livraison::with(['commande.client', 'livreur'])
            ->when($request->filled('statut'),      fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('sans_livreur'),fn($q) => $q->whereNull('livreur_id'))
            ->latest()
            ->paginate(20);
 
        return view('admin.livraisons', compact('livraisons'));
    }
 
    public function assignerForm(int $id)
    {
        $livraison = Livraison::with('commande.client')->findOrFail($id);
        $livreurs  = User::where('role', 'livreur')
                         ->where('statut', 'actif')
                         ->get();
 
        return view('admin.livraison_assigner', compact('livraison', 'livreurs'));
    }
 
    public function assigner(Request $request, int $id)
    {
        $livraison = Livraison::findOrFail($id);
 
        $validated = $request->validate([
            'livreur_id'            => 'required|exists:users,id',
            'transporteur'          => 'nullable|string|max:100',
            'numero_suivi'          => 'nullable|string|max:100',
            'date_livraison_prevue' => 'nullable|date|after_or_equal:today',
            'frais_livraison'       => 'nullable|numeric|min:0',
        ]);
 
        $livreur = User::findOrFail($validated['livreur_id']);
 
        if ($livreur->role !== 'livreur') {
            return back()->with('error', "Cet utilisateur n'est pas un livreur.");
        }
 
        $livraison->update([
            'livreur_id'            => $validated['livreur_id'],
            'transporteur'          => $validated['transporteur'] ?? null,
            'numero_suivi'          => $validated['numero_suivi'] ?? null,
            'date_livraison_prevue' => $validated['date_livraison_prevue'] ?? null,
            'frais_livraison'       => $validated['frais_livraison'] ?? 0,
        ]);

        $livraison->load('commande');

        // Notifier le livreur
        Notification::envoyer(
            $livreur->id,
            'livraison_assignee',
            '📬 Nouvelle livraison assignée',
            "Commande #{$livraison->commande_id} vous a été assignée.",
            ['livraison_id' => $livraison->id]
        );

        if ($livraison->commande?->client_id) {
            Notification::envoyer(
                $livraison->commande->client_id,
                'livraison_assignee',
                '📦 Livreur assigné',
                "Un livreur a été assigné à votre commande #{$livraison->commande_id}.",
                ['commande_id' => $livraison->commande_id]
            );
        }
 
        return redirect()->route('admin.livraisons')
            ->with('success', "Livraison assignée à {$livreur->nom_complet}.");
    }
}
 