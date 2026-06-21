<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\FournisseurMateriau;
use App\Models\FournisseurOutil;
use App\Models\MateriauFormation;
use App\Models\Notification;
use App\Models\OutilFormation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FournisseurAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Fournisseur::query()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('q')) {
            $query->where('nom', 'ilike', '%' . $request->q . '%');
        }

        $fournisseurs = $query->orderBy('nom')->paginate(15);

        $enAttente = Fournisseur::where('statut', 'inactif')->whereNotNull('user_id')->count();

        return view('admin.fournisseurs.index', compact('fournisseurs', 'enAttente'));
    }

    public function create()
    {
        return view('admin.fournisseurs.form');
    }

    public function store(Request $request)
    {
        $rules = [
            'nom'                 => 'required|string|max:150',
            'type'                => 'required|in:local,national,en_ligne',
            'statut'              => 'required|in:actif,inactif',
            'telephone'           => 'nullable|string|max:20',
            'whatsapp'            => 'nullable|string|max:20',
            'adresse'             => 'nullable|string|max:300',
            'ville'               => 'nullable|string|max:100',
            'site_web'            => 'nullable|url|max:300',
            'logo'                => 'nullable|image|max:2048',
            'remise_cooperative'  => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
            'creer_acces'         => 'nullable|boolean',
        ];

        if ($request->boolean('creer_acces')) {
            $rules['email']    = 'required|email|max:150|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['email'] = 'nullable|email|max:150';
        }

        $validated = $request->validate($rules);

        if ($validated['type'] === 'en_ligne' && empty($validated['site_web'])) {
            return back()->withErrors([
                'site_web' => 'Le site web est obligatoire pour un fournisseur en ligne.',
            ])->withInput();
        }

        $userId = null;

        if ($request->boolean('creer_acces')) {
            $nameParts = explode(' ', $validated['nom'], 2);

            $user = User::create([
                'nom'       => $nameParts[0],
                'prenom'    => $nameParts[1] ?? $nameParts[0],
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'telephone' => $validated['telephone'] ?? null,
                'ville'     => $validated['ville'] ?? null,
                'role'      => 'fournisseur',
                'statut'    => 'actif',
            ]);
            $userId = $user->id;

            Notification::envoyer(
                $user->id,
                'compte_cree',
                '🏭 Compte fournisseur créé',
                'Bienvenue sur Tissu Artisanal ! Vous pouvez gérer votre catalogue ' .
                'de produits depuis votre espace fournisseur.',
                []
            );
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('fournisseurs', 'public');
        }

        $fournisseurData = collect($validated)
            ->except(['creer_acces', 'password'])
            ->toArray();
        $fournisseurData['user_id'] = $userId;

        $fournisseur = Fournisseur::create($fournisseurData);

        $msg = $userId
            ? "Fournisseur « {$fournisseur->nom} » créé avec accès de connexion ({$validated['email']})."
            : "Fournisseur « {$fournisseur->nom} » créé en tant que fiche business, sans accès de connexion.";

        return redirect()->route('admin.fournisseurs.index')->with('success', $msg);
    }

    public function edit(int $id)
    {
        $fournisseur = Fournisseur::with('user')->findOrFail($id);

        return view('admin.fournisseurs.form', compact('fournisseur'));
    }

    public function update(Request $request, int $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $validated   = $this->validateFournisseur($request);

        if ($validated['type'] === 'en_ligne' && empty($validated['site_web'])) {
            return back()->withErrors([
                'site_web' => 'Le site web est obligatoire pour un fournisseur en ligne.',
            ])->withInput();
        }

        if ($request->hasFile('logo')) {
            if ($fournisseur->logo) {
                Storage::disk('public')->delete($fournisseur->logo);
            }
            $validated['logo'] = $request->file('logo')->store('fournisseurs', 'public');
        }

        $fournisseur->update($validated);

        return back()->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function activer(int $id)
    {
        $fournisseur = Fournisseur::with('user')->findOrFail($id);
        $fournisseur->update(['statut' => 'actif']);

        if ($fournisseur->user_id) {
            Notification::envoyer(
                $fournisseur->user_id,
                'fournisseur_active',
                '✅ Compte fournisseur activé',
                "Votre compte fournisseur « {$fournisseur->nom} » a été validé. " .
                'Vous pouvez maintenant gérer votre catalogue de produits.',
                []
            );
        }

        return back()->with('success', "Fournisseur « {$fournisseur->nom} » activé.");
    }

    public function destroy(int $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);

        if ($fournisseur->materiaux()->exists() || $fournisseur->outils()->exists()) {
            return back()->with('error',
                'Impossible de supprimer : ce fournisseur est lié à des matériaux/outils. ' .
                'Désactivez-le plutôt (statut = inactif).');
        }

        if ($fournisseur->logo) {
            Storage::disk('public')->delete($fournisseur->logo);
        }

        $fournisseur->delete();

        return back()->with('success', 'Fournisseur supprimé.');
    }

    public function produits(int $id)
    {
        $fournisseur = Fournisseur::with([
            'materiaux.materiau.formation',
            'outils.outil.formation',
        ])->findOrFail($id);

        $materiauxOptions = MateriauFormation::with('formation')
            ->orderBy('formation_id')
            ->orderBy('ordre')
            ->get()
            ->groupBy(fn ($m) => $m->formation?->titre ?? 'Sans formation');

        $outilsOptions = OutilFormation::with('formation')
            ->orderBy('formation_id')
            ->orderBy('ordre')
            ->get()
            ->groupBy(fn ($o) => $o->formation?->titre ?? 'Sans formation');

        return view('admin.fournisseurs.produits', compact(
            'fournisseur',
            'materiauxOptions',
            'outilsOptions'
        ));
    }

    public function ajouterMateriau(Request $request, int $fournisseurId)
    {
        $validated = $request->validate([
            'materiau_id'         => 'required|exists:materiaux_formation,id',
            'nom_produit'         => 'required|string|max:200',
            'reference_produit'   => 'nullable|string|max:100',
            'prix_unitaire'       => 'required|numeric|min:0',
            'unite_prix'          => 'nullable|string|max:50',
            'url_produit'         => 'nullable|url|max:500',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
            'est_recommande'      => 'nullable|boolean',
            'stock_disponible'    => 'nullable|boolean',
        ]);

        FournisseurMateriau::create([
            'fournisseur_id'          => $fournisseurId,
            'materiau_id'             => $validated['materiau_id'],
            'nom_produit_fournisseur' => $validated['nom_produit'],
            'reference_produit'       => $validated['reference_produit'] ?? null,
            'prix_unitaire'           => $validated['prix_unitaire'],
            'unite_prix'              => $validated['unite_prix'] ?? null,
            'url_produit'             => $validated['url_produit'] ?? null,
            'delai_livraison_min'     => $validated['delai_livraison_min'] ?? null,
            'delai_livraison_max'     => $validated['delai_livraison_max'] ?? null,
            'est_recommande'          => $request->boolean('est_recommande'),
            'stock_disponible'        => $request->boolean('stock_disponible', true),
        ]);

        return back()->with('success', 'Produit ajouté au catalogue de matériaux.');
    }

    public function ajouterOutil(Request $request, int $fournisseurId)
    {
        $validated = $request->validate([
            'outil_id'          => 'required|exists:outils_formation,id',
            'nom_produit'       => 'required|string|max:200',
            'reference_produit' => 'nullable|string|max:100',
            'prix_unitaire'     => 'required|numeric|min:0',
            'unite_prix'        => 'nullable|string|max:50',
            'url_produit'       => 'nullable|url|max:500',
            'est_recommande'    => 'nullable|boolean',
            'stock_disponible'  => 'nullable|boolean',
        ]);

        FournisseurOutil::create([
            'fournisseur_id'          => $fournisseurId,
            'outil_id'                => $validated['outil_id'],
            'nom_produit_fournisseur' => $validated['nom_produit'],
            'reference_produit'       => $validated['reference_produit'] ?? null,
            'prix_unitaire'           => $validated['prix_unitaire'],
            'unite_prix'              => $validated['unite_prix'] ?? null,
            'url_produit'             => $validated['url_produit'] ?? null,
            'est_recommande'          => $request->boolean('est_recommande'),
            'stock_disponible'        => $request->boolean('stock_disponible', true),
        ]);

        return back()->with('success', 'Produit ajouté au catalogue d\'outils.');
    }

    private function validateFournisseur(Request $request): array
    {
        return $request->validate([
            'nom'                 => 'required|string|max:150',
            'type'                => 'required|in:local,national,en_ligne',
            'statut'              => 'required|in:actif,inactif',
            'email'               => 'nullable|email|max:150',
            'telephone'           => 'nullable|string|max:20',
            'whatsapp'            => 'nullable|string|max:20',
            'adresse'             => 'nullable|string|max:300',
            'ville'               => 'nullable|string|max:100',
            'site_web'            => 'nullable|url|max:300',
            'logo'                => 'nullable|image|max:2048',
            'remise_cooperative'  => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
        ]);
    }
}
