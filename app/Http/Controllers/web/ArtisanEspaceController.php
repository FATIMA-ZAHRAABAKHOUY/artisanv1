<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Artisan;
use App\Models\Notification;
use App\Models\RessourceFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class ArtisanEspaceController extends Controller
{
    // GET /artisan/dashboard
    public function dashboard()
    {
        return view('artisans.dashboard');
    }

    // GET /artisan/commandes
    public function commandes(Request $request)
    {
        $artisanId = auth()->user()->artisan->id;

        $commandes = \DB::table('commandes')
            ->join('lignes_commande', 'lignes_commande.commande_id', '=', 'commandes.id')
            ->join('produits', 'produits.id', '=', 'lignes_commande.produit_id')
            ->join('users', 'users.id', '=', 'commandes.client_id')
            ->where('produits.artisan_id', $artisanId)
            ->when($request->filled('statut'), fn($q) => $q->where('commandes.statut', $request->statut))
            ->selectRaw("DISTINCT commandes.id, commandes.statut, commandes.total_ttc,
                         commandes.created_at, commandes.ville,
                         users.nom || ' ' || users.prenom as client,
                         users.telephone as client_tel")
            ->orderByDesc('commandes.created_at')
            ->paginate(15);

        return view('artisans.commandes', compact('commandes'));
    }

    // GET /artisan/produits
    public function produits(Request $request)
    {
        $produits = auth()->user()->artisan->produits()
            ->with('categorie')
            ->when($request->filled('actif'), fn($q) => $q->where('is_active', $request->actif))
            ->latest()
            ->paginate(15);

        return view('artisans.produits', compact('produits'));
    }

    // GET /artisan/produits/creer
    public function createProduit()
    {
        return view('artisans.produit_form', [
            'maxUploadMo'     => $this->maxUploadMoLabel(),
            'maxUploadBytes'  => $this->imageMaxKb() * 1024,
        ]);
    }

    // POST /artisan/produits
    public function storeProduit(Request $request)
    {
        if ($redirect = $this->guardInvalidUploads($request, 'images')) {
            return $redirect;
        }

        $maxKb = $this->imageMaxKb();

        $validated = $request->validate(
            $this->produitRules($maxKb),
            $this->produitImageMessages($maxKb)
        );

        $artisan     = auth()->user()->artisan;
        $imagesPaths = $this->storeProduitImages($request, $artisan->id);

        \App\Models\Produit::create([
            'artisan_id'   => $artisan->id,
            'categorie_id' => $validated['categorie_id'] ?? null,
            'nom'          => $validated['nom'],
            'description'  => $validated['description'] ?? null,
            'prix'         => $validated['prix'],
            'stock'        => $validated['stock'],
            'images'       => $imagesPaths,
            'poids'        => $validated['poids'] ?? null,
            'dimensions'   => $validated['dimensions'] ?? null,
            'is_active'    => true,
            'slug'         => \Str::slug($validated['nom']) . '-' . \Str::random(6),
        ]);

        return redirect()->route('artisan.produits')
            ->with('success', 'Produit publié avec succès !');
    }

    // GET /artisan/produits/{id}/edit
    public function editProduit(int $id)
    {
        $produit = auth()->user()->artisan->produits()->findOrFail($id);
        return view('artisans.produit_form', [
            'produit'         => $produit,
            'maxUploadMo'     => $this->maxUploadMoLabel(),
            'maxUploadBytes'  => $this->imageMaxKb() * 1024,
        ]);
    }

    // PUT /artisan/produits/{id}
    public function updateProduit(Request $request, int $id)
    {
        $produit = auth()->user()->artisan->produits()->findOrFail($id);

        if ($redirect = $this->guardInvalidUploads($request, 'images')) {
            return $redirect;
        }

        $maxKb = $this->imageMaxKb();

        $validated = $request->validate(
            array_merge($this->produitRules($maxKb), ['is_active' => 'boolean']),
            $this->produitImageMessages($maxKb)
        );

        $newImages = $this->storeProduitImages($request, $produit->artisan_id);
        if ($newImages !== []) {
            $validated['images'] = array_values(array_merge($produit->images ?? [], $newImages));
        }

        $produit->update($validated);

        return redirect()->route('artisan.produits')
            ->with('success', 'Produit mis à jour.');
    }

    // DELETE /artisan/produits/{id}
    public function destroyProduit(int $id)
    {
        $produit = auth()->user()->artisan->produits()->findOrFail($id);
        $produit->update(['is_active' => false]);

        return back()->with('success', 'Produit désactivé.');
    }

    // GET /artisan/formations
    public function formations()
    {
        $formations = auth()->user()->artisan->formations()
            ->withCount([
                'inscriptions',
                'inscriptions as en_cours' =>
                    fn ($q) => $q->where('statut_inscription', 'en_cours'),
            ])
            ->latest()
            ->paginate(10);

        return view('artisans.formations.index', compact('formations'));
    }

    // GET /artisan/formations/creer
    public function createFormation()
    {
        return view('artisans.formation_form');
    }

    // POST /artisan/formations
    public function storeFormation(Request $request)
    {
        $validated = $request->validate([
            'titre'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'date_debut'  => 'required|date|after_or_equal:today',
            'date_fin'    => 'required|date|after:date_debut',
            'prix'        => 'required|numeric|min:0',
            'places_max'  => 'required|integer|min:1|max:50',
            'lieu'        => 'nullable|string|max:200',
            'image'       => 'nullable|image|max:3072',
        ]);

        $artisan   = auth()->user()->artisan;
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store("formations/{$artisan->id}", 'public');
        }

        Formation::create([
            ...$validated,
            'artisan_id' => $artisan->id,
            'image'      => $imagePath,
            'is_active'  => true,
        ]);

        return redirect()->route('artisan.formations')
            ->with('success', 'Formation créée avec succès !');
    }

    // GET /artisan/formations/{id}/edit
    public function editFormation(int $id)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($id);
        return view('artisans.formation_form', compact('formation'));
    }

    // PUT /artisan/formations/{id}
    public function updateFormation(Request $request, int $id)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($id);
        $validated = $request->validate([
            'titre'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after:date_debut',
            'prix'        => 'required|numeric|min:0',
            'places_max'  => 'required|integer|min:1',
            'lieu'        => 'nullable|string|max:200',
            'is_active'   => 'boolean',
        ]);

        $formation->update($validated);

        return redirect()->route('artisan.formations')
            ->with('success', 'Formation mise à jour.');
    }

    // GET /artisan/formations/{id}/contenu
    public function gererContenu(int $id)
    {
        $formation = auth()->user()->artisan->formations()
            ->with(['etapes', 'materiaux', 'outils', 'ressources'])
            ->findOrFail($id);

        return view('artisans.formation_contenu', compact('formation'));
    }

    // ── ÉTAPES ──────────────────────────────────────────────
    public function storeEtape(Request $request, int $formationId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);

        $validated = $request->validate([
            'numero_ordre'     => 'required|integer|min:1',
            'titre'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'duree_minutes'    => 'nullable|integer|min:1',
            'objectif'         => 'nullable|string',
            'materiaux_requis' => 'nullable|string',
        ]);

        $formation->etapes()->create($validated);

        return back()->with('success', 'Étape ajoutée au programme.');
    }

    public function updateEtape(Request $request, int $formationId, int $etapeId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $etape = $formation->etapes()->findOrFail($etapeId);

        $validated = $request->validate([
            'numero_ordre'     => 'required|integer|min:1',
            'titre'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'duree_minutes'    => 'nullable|integer|min:1',
            'objectif'         => 'nullable|string',
            'materiaux_requis' => 'nullable|string',
        ]);

        $etape->update($validated);

        return back()->with('success', 'Étape mise à jour.');
    }

    public function destroyEtape(int $formationId, int $etapeId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $formation->etapes()->findOrFail($etapeId)->delete();

        return back()->with('success', 'Étape supprimée.');
    }

    // ── MATÉRIAUX ───────────────────────────────────────────
    public function storeMateriau(Request $request, int $formationId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'type'        => 'required|in:fil,laine,coton,soie,lin,raphia,corde,autre',
            'couleur'     => 'nullable|string|max:100',
            'quantite'    => 'required|numeric|min:0',
            'unite'       => 'required|in:metre,gramme,kilogramme,pelote,bobine,piece,autre',
            'description' => 'nullable|string',
            'est_fourni'  => 'boolean',
            'image'       => 'nullable|image|max:2048',
            'ordre'       => 'nullable|integer|min:0',
        ]);

        $validated['est_fourni'] = $request->boolean('est_fourni');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('materiaux', 'public');
        }

        $formation->materiaux()->create($validated);

        return back()->with('success', 'Matériau ajouté.');
    }

    public function updateMateriau(Request $request, int $formationId, int $materiauId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $materiau = $formation->materiaux()->findOrFail($materiauId);

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'type'        => 'required|in:fil,laine,coton,soie,lin,raphia,corde,autre',
            'couleur'     => 'nullable|string|max:100',
            'quantite'    => 'required|numeric|min:0',
            'unite'       => 'required|in:metre,gramme,kilogramme,pelote,bobine,piece,autre',
            'description' => 'nullable|string',
            'est_fourni'  => 'boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        $validated['est_fourni'] = $request->boolean('est_fourni');

        if ($request->hasFile('image')) {
            if ($materiau->image) {
                Storage::disk('public')->delete($materiau->image);
            }
            $validated['image'] = $request->file('image')->store('materiaux', 'public');
        }

        $materiau->update($validated);

        return back()->with('success', 'Matériau mis à jour.');
    }

    public function destroyMateriau(int $formationId, int $materiauId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $formation->materiaux()->findOrFail($materiauId)->delete();

        return back()->with('success', 'Matériau supprimé.');
    }

    // ── OUTILS ──────────────────────────────────────────────
    public function storeOutil(Request $request, int $formationId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string',
            'quantite'    => 'required|integer|min:1',
            'est_fourni'  => 'boolean',
            'image'       => 'nullable|image|max:2048',
            'lien_achat'  => 'nullable|url|max:500',
            'ordre'       => 'nullable|integer|min:0',
        ]);

        $validated['est_fourni'] = $request->boolean('est_fourni');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('outils', 'public');
        }

        $formation->outils()->create($validated);

        return back()->with('success', 'Outil ajouté.');
    }

    public function updateOutil(Request $request, int $formationId, int $outilId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $outil = $formation->outils()->findOrFail($outilId);

        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'description' => 'nullable|string',
            'quantite'    => 'required|integer|min:1',
            'est_fourni'  => 'boolean',
            'image'       => 'nullable|image|max:2048',
            'lien_achat'  => 'nullable|url|max:500',
        ]);

        $validated['est_fourni'] = $request->boolean('est_fourni');

        if ($request->hasFile('image')) {
            if ($outil->image) {
                Storage::disk('public')->delete($outil->image);
            }
            $validated['image'] = $request->file('image')->store('outils', 'public');
        }

        $outil->update($validated);

        return back()->with('success', 'Outil mis à jour.');
    }

    public function destroyOutil(int $formationId, int $outilId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $formation->outils()->findOrFail($outilId)->delete();

        return back()->with('success', 'Outil supprimé.');
    }

    // ── RESSOURCES ──────────────────────────────────────────
    public function storeRessource(Request $request, int $formationId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);

        $validated = $request->validate([
            'type'           => 'required|in:video,document_pdf,image,lien_externe',
            'titre'          => 'required|string|max:200',
            'description'    => 'nullable|string',
            'source_type'    => 'required|in:upload,url',
            'url'            => 'required_if:source_type,url|nullable|url|max:1000',
            'fichier'        => 'required_if:source_type,upload|nullable|file|max:51200',
            'duree_secondes' => 'nullable|integer|min:0',
            'auteur'         => 'nullable|string|max:150',
            'nb_pages'       => 'nullable|integer|min:1',
            'est_public'     => 'boolean',
            'ordre'          => 'nullable|integer|min:0',
        ]);

        $validated['est_public'] = $request->boolean('est_public');

        if ($validated['type'] === 'lien_externe') {
            $request->merge(['source_type' => 'url']);
            $validated['source_type'] = 'url';
        }

        if ($redirect = $this->processRessourceSource($request, $validated, $formationId)) {
            return $redirect;
        }

        unset($validated['source_type'], $validated['fichier']);

        $formation->ressources()->create($validated);

        return back()->with('success', 'Ressource ajoutée avec succès.');
    }

    public function updateRessource(Request $request, int $formationId, int $ressourceId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $ressource = $formation->ressources()->findOrFail($ressourceId);

        $validated = $request->validate([
            'type'           => 'required|in:video,document_pdf,image,lien_externe',
            'titre'          => 'required|string|max:200',
            'description'    => 'nullable|string',
            'source_type'    => 'required|in:upload,url',
            'url'            => 'required_if:source_type,url|nullable|url|max:1000',
            'fichier'        => 'nullable|file|max:51200',
            'duree_secondes' => 'nullable|integer|min:0',
            'auteur'         => 'nullable|string|max:150',
            'nb_pages'       => 'nullable|integer|min:1',
            'est_public'     => 'boolean',
        ]);

        $validated['est_public'] = $request->boolean('est_public');

        if ($validated['type'] === 'lien_externe') {
            $request->merge(['source_type' => 'url']);
            $validated['source_type'] = 'url';
        }

        if ($redirect = $this->processRessourceSource($request, $validated, $formationId, $ressource)) {
            return $redirect;
        }

        unset($validated['source_type'], $validated['fichier']);

        $ressource->update($validated);

        return back()->with('success', 'Ressource mise à jour.');
    }

    public function destroyRessource(int $formationId, int $ressourceId)
    {
        $formation = auth()->user()->artisan->formations()->findOrFail($formationId);
        $ressource = $formation->ressources()->findOrFail($ressourceId);

        if ($ressource->isUploadedFile()) {
            Storage::disk('public')->delete($ressource->url);
        }

        $ressource->delete();

        return back()->with('success', 'Ressource supprimée.');
    }

    private function processRessourceSource(
        Request $request,
        array &$validated,
        int $formationId,
        ?RessourceFormation $existing = null
    ): ?RedirectResponse {
        $sourceType = $validated['source_type'] ?? 'url';

        if ($sourceType === 'upload') {
            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $ext  = strtolower($file->getClientOriginalExtension());

                $allowedByType = [
                    'video'        => ['mp4', 'mov', 'avi', 'webm'],
                    'document_pdf' => ['pdf'],
                    'image'        => ['jpg', 'jpeg', 'png', 'webp'],
                ];

                if (isset($allowedByType[$validated['type']])
                    && ! in_array($ext, $allowedByType[$validated['type']], true)) {
                    return back()->withErrors([
                        'fichier' => 'Le fichier doit être de type : '
                            . implode(', ', $allowedByType[$validated['type']]),
                    ])->withInput();
                }

                if ($existing && $existing->isUploadedFile()) {
                    Storage::disk('public')->delete($existing->url);
                }

                $validated['url']       = $file->store("formations/{$formationId}/ressources", 'public');
                $validated['taille_ko'] = (int) round($file->getSize() / 1024);
            } elseif ($existing && $existing->url) {
                $validated['url']       = $existing->url;
                $validated['taille_ko'] = $existing->taille_ko;
            } else {
                return back()->withErrors([
                    'fichier' => 'Veuillez sélectionner un fichier à uploader.',
                ])->withInput();
            }
        } else {
            if (empty($validated['url'])) {
                return back()->withErrors([
                    'url' => 'Veuillez saisir une URL valide.',
                ])->withInput();
            }

            if ($existing && $existing->isUploadedFile()) {
                Storage::disk('public')->delete($existing->url);
            }
        }

        return null;
    }

    private function produitRules(int $maxKb): array
    {
        return [
            'nom'          => 'required|string|max:200',
            'description'  => 'nullable|string',
            'prix'         => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
            'poids'        => 'nullable|numeric|min:0',
            'dimensions'   => 'nullable|string|max:100',
            'images'       => 'nullable|array|max:5',
            'images.*'     => "nullable|file|mimes:jpeg,jpg,png,webp|max:{$maxKb}",
        ];
    }

    private function produitImageMessages(int $maxKb): array
    {
        $maxMo = number_format($maxKb / 1024, 1, ',', ' ');

        return [
            'images.*.uploaded' => "Le téléversement a échoué. Utilisez JPG, PNG ou WebP (max {$maxMo} Mo par photo).",
            'images.*.mimes'    => 'Format non supporté. Utilisez JPG, PNG ou WebP.',
            'images.*.max'      => "Chaque photo ne doit pas dépasser {$maxMo} Mo.",
        ];
    }

    private function guardInvalidUploads(Request $request, string $field): ?RedirectResponse
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        foreach ($request->file($field) as $index => $file) {
            if ($file instanceof UploadedFile && ! $file->isValid()) {
                return back()
                    ->withErrors(["{$field}.{$index}" => $this->uploadErrorMessage($file->getError())])
                    ->withInput();
            }
        }

        return null;
    }

    private function storeProduitImages(Request $request, int $artisanId): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $paths = [];

        foreach ($request->file('images') as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $paths[] = $file->store("produits/{$artisanId}", 'public');
        }

        return $paths;
    }

    private function maxUploadMoLabel(): string
    {
        return number_format($this->imageMaxKb() / 1024, 1, ',', ' ');
    }

    private function imageMaxKb(): int
    {
        $uploadMax = $this->parseIniSize(ini_get('upload_max_filesize'));
        $postMax   = $this->parseIniSize(ini_get('post_max_size'));
        $bytes     = min($uploadMax, (int) floor($postMax * 0.8));

        return max(512, (int) floor($bytes / 1024));
    }

    private function parseIniSize(string|false $value): int
    {
        if ($value === false || $value === '') {
            return 2 * 1024 * 1024;
        }

        $value = trim($value);
        $unit  = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g'     => (int) ($number * 1024 * 1024 * 1024),
            'm'     => (int) ($number * 1024 * 1024),
            'k'     => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        $maxMo = number_format($this->imageMaxKb() / 1024, 1, ',', ' ');

        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                "La photo est trop volumineuse (maximum {$maxMo} Mo). Compressez l'image ou augmentez upload_max_filesize dans php.ini.",
            UPLOAD_ERR_PARTIAL =>
                'Le téléversement a été interrompu. Réessayez.',
            UPLOAD_ERR_NO_FILE =>
                'Aucune photo sélectionnée.',
            default =>
                "Le téléversement a échoué. Vérifiez le format (JPG, PNG, WebP) et la taille (max {$maxMo} Mo).",
        };
    }
}