<?php

namespace App\Http\Controllers\Web\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Support::with(['user', 'livraison'])
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);
 
        return view('admin.support', compact('tickets'));
    }
 
    public function updateStatut(Request $request, int $id)
    {
        $ticket = Support::findOrFail($id);
        $validated = $request->validate([
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
        ]);
 
        $ticket->update($validated);
 
        // Notifier l'utilisateur si résolu
        if ($validated['statut'] === 'resolu') {
            Notification::envoyer(
                $ticket->user_id,
                'ticket_resolu',
                '✅ Ticket support résolu',
                "Votre demande « {$ticket->objet} » a été résolue par notre équipe.",
                ['ticket_id' => $ticket->id]
            );
        }
 
        return back()->with('success', "Ticket mis à jour → {$validated['statut']}");
    }
}
 