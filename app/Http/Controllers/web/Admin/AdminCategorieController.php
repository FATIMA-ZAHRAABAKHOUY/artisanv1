<?php

namespace App\Http\Controllers\Web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminCategorieController extends Controller
{
    public function index()
    {
        return view('admin.categories');
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100|unique:categories,nom',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'image'       => 'nullable|image|max:2048',
        ]);
 
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }
 
        Categorie::create([
            ...$validated,
            'image' => $imagePath,
            'slug'  => Str::slug($validated['nom']),
        ]);
 
        return back()->with('success', "Catégorie « {$validated['nom']} » créée.");
    }
 
    public function update(Request $request, int $id)
    {
        $categorie = Categorie::findOrFail($id);
        $validated = $request->validate([
            'nom'         => "required|string|max:100|unique:categories,nom,{$id}",
            'description' => 'nullable|string',
        ]);
 
        $categorie->update([
            ...$validated,
            'slug' => Str::slug($validated['nom']),
        ]);
 
        return back()->with('success', 'Catégorie mise à jour.');
    }
 
    public function destroy(int $id)
    {
        $categorie = Categorie::withCount('produits')->findOrFail($id);
 
        if ($categorie->produits_count > 0) {
            return back()->with('error',
                "Impossible : {$categorie->produits_count} produit(s) associé(s).");
        }
 
        $categorie->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
 
 