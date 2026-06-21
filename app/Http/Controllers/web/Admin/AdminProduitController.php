<?php

namespace App\Http\Controllers\Web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminProduitController extends Controller
{
    public function index(Request $request)
    {
        $produits = Produit::with(['artisan.user', 'categorie'])
            ->when($request->filled('q'), fn($q) =>
                $q->where('nom', 'ilike', "%{$request->q}%")
            )
            ->when($request->filled('categorie_id'), fn($q) =>
                $q->where('categorie_id', $request->categorie_id)
            )
            ->when($request->filled('actif'), fn($q) =>
                $q->where('is_active', $request->actif)
            )
            ->latest()
            ->paginate(20);
 
        return view('admin.produits', compact('produits'));
    }
 
    public function toggle(int $id)
    {
        $produit = Produit::findOrFail($id);
        $produit->update(['is_active' => !$produit->is_active]);
 
        return back()->with('success',
            "Produit « {$produit->nom} » "
            . ($produit->is_active ? 'activé' : 'désactivé') . '.');
    }
}
 
 