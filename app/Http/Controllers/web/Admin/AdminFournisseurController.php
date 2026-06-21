<?php

namespace App\Http\Controllers\Web\Admin;

 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminFournisseurController extends Controller
{
    public function index(Request $request)
    {
        $fournisseurs = Fournisseur::with('specialites')
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->orderBy('nom')
            ->paginate(15);
 
        return view('admin.fournisseurs', compact('fournisseurs'));
    }
 
    public function create()
    {
        return view('admin.fournisseur_form');
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'                 => 'required|string|max:150',
            'type'                => 'required|in:local,national,en_ligne',
            'email'               => 'nullable|email',
            'telephone'           => 'nullable|string|max:20',
            'whatsapp'            => 'nullable|string|max:20',
            'adresse'             => 'nullable|string',
            'ville'               => 'nullable|string|max:100',
            'region'              => 'nullable|string|max:100',
            'site_web'            => 'nullable|url',
            'description'         => 'nullable|string',
            'remise_cooperative'  => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
            'specialites'         => 'nullable|array',
            'specialites.*'       => 'string|max:100',
        ]);
 
        // OCL : en_ligne → site_web obligatoire
        if ($validated['type'] === 'en_ligne' && empty($validated['site_web'])) {
            return back()->with('error', 'Un fournisseur en ligne doit avoir un site web.');
        }
 
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('fournisseurs/logos', 'public');
        }
 
        $fournisseur = Fournisseur::create([
            ...$validated,
            'logo'   => $logoPath,
            'statut' => 'actif',
        ]);
 
        // Spécialités
        if (!empty($validated['specialites'])) {
            foreach ($validated['specialites'] as $spec) {
                \App\Models\FournisseurSpecialite::create([
                    'fournisseur_id' => $fournisseur->id,
                    'specialite'     => $spec,
                ]);
            }
        }
 
        return redirect()->route('admin.fournisseurs')
            ->with('success', 'Fournisseur créé avec succès !');
    }
 
    public function edit(int $id)
    {
        $fournisseur = Fournisseur::with('specialites')->findOrFail($id);
        return view('admin.fournisseur_form', compact('fournisseur'));
    }
 
    public function update(Request $request, int $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $validated   = $request->validate([
            'nom'                 => 'required|string|max:150',
            'type'                => 'required|in:local,national,en_ligne',
            'statut'              => 'sometimes|in:actif,inactif,suspendu',
            'email'               => 'nullable|email',
            'telephone'           => 'nullable|string|max:20',
            'site_web'            => 'nullable|url',
            'description'         => 'nullable|string',
            'remise_cooperative'  => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
        ]);
 
        $fournisseur->update($validated);
 
        return redirect()->route('admin.fournisseurs')
            ->with('success', 'Fournisseur mis à jour.');
    }
 
    public function destroy(int $id)
    {
        Fournisseur::findOrFail($id)->update(['statut' => 'inactif']);
        return back()->with('success', 'Fournisseur désactivé.');
    }
}
 
 