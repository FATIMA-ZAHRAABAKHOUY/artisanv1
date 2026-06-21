<?php

namespace App\Http\Controllers\Web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminArtisanController extends Controller
{
    public function index(Request $request)
    {
        $artisans = Artisan::with('user')
            ->withCount('produits')
            ->when($request->get('filtre') === 'en_attente',
                fn($q) => $q->where('is_verified', false)
            )
            ->latest()
            ->paginate(20);
 
        return view('admin.artisans', compact('artisans'));
    }
 
    public function valider(int $id)
    {
        $artisan = Artisan::with('user')->findOrFail($id);
        $artisan->update(['is_verified' => true, 'statut' => 'actif']);
 
        Notification::envoyer(
            $artisan->user_id,
            'artisan_valide',
            '✅ Compte artisan validé',
            'Votre compte artisan a été validé. Vous pouvez maintenant publier vos produits.',
            ['artisan_id' => $artisan->id]
        );
 
        return back()->with('success', "{$artisan->user->nom_complet} validé avec succès !");
    }
 
    public function suspendre(int $id)
    {
        $artisan = Artisan::with('user')->findOrFail($id);
        $artisan->update(['statut' => 'suspendu']);
        $artisan->user->update(['statut' => 'suspendu']);
 
        Notification::envoyer(
            $artisan->user_id,
            'artisan_suspendu',
            'Compte suspendu',
            "Votre compte artisan a été suspendu. Contactez l'administration.",
            []
        );
 
        return back()->with('success', "{$artisan->user->nom_complet} suspendu.");
    }
}
 
 
