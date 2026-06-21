<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\FournisseurSpecialite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FournisseurController extends Controller
{
    // ────────────────────────────────────────────────────────────
    // GET /api/fournisseurs
    // Public — liste avec filtres
    // ────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Fournisseur::with('specialites')
                            ->where('statut', 'actif');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('ville')) {
            $query->where('ville', 'ilike', "%{$request->ville}%");
        }

        if ($request->filled('specialite')) {
            $query->whereHas('specialites', fn($q) =>
                $q->where('specialite', 'ilike', "%{$request->specialite}%")
            );
        }

        $fournisseurs = $query->orderBy('nom')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $fournisseurs->map(fn($f) => $this->fournisseurResource($f)),
            'meta'    => [
                'total'        => $fournisseurs->total(),
                'current_page' => $fournisseurs->currentPage(),
                'last_page'    => $fournisseurs->lastPage(),
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // GET /api/fournisseurs/{id}
    // ────────────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::with(['specialites', 'artisans.user'])
                                  ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->fournisseurResource($fournisseur, true),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/admin/fournisseurs
    // Admin uniquement
    // ────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'                  => 'required|string|max:150',
            'type'                 => 'required|in:local,national,en_ligne',
            'email'                => 'nullable|email|max:150',
            'telephone'            => 'nullable|string|max:20',
            'whatsapp'             => 'nullable|string|max:20',
            'adresse'              => 'nullable|string',
            'ville'                => 'required_if:type,local|nullable|string|max:100',
            'region'               => 'nullable|string|max:100',
            'site_web'             => 'required_if:type,en_ligne|nullable|url|max:300',
            'description'          => 'nullable|string',
            'remise_cooperative'   => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min'  => 'nullable|integer|min:0',
            'delai_livraison_max'  => 'nullable|integer|min:0',
            'specialites'          => 'nullable|array',
            'specialites.*'        => 'string|max:100',
            'logo'                 => 'nullable|image|max:2048',
        ]);

        // OCL : en_ligne → site_web obligatoire
        if ($validated['type'] === 'en_ligne' && empty($validated['site_web'])) {
            return response()->json([
                'success' => false,
                'message' => 'OCL : Un fournisseur en ligne doit avoir un site web.',
            ], 422);
        }

        // OCL : local → ville obligatoire
        if ($validated['type'] === 'local' && empty($validated['ville'])) {
            return response()->json([
                'success' => false,
                'message' => 'OCL : Un fournisseur local doit avoir une ville.',
            ], 422);
        }

        // Logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('fournisseurs/logos', 'public');
        }

        $fournisseur = Fournisseur::create([
            ...$validated,
            'logo'   => $logoPath,
            'statut' => 'actif',
        ]);

        // Ajouter les spécialités
        if (!empty($validated['specialites'])) {
            $specialites = collect($validated['specialites'])
                ->map(fn($s) => ['fournisseur_id' => $fournisseur->id, 'specialite' => $s])
                ->toArray();
            FournisseurSpecialite::insert($specialites);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur créé avec succès.',
            'data'    => $this->fournisseurResource($fournisseur->load('specialites')),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────
    // PUT /api/admin/fournisseurs/{id}
    // ────────────────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);

        $validated = $request->validate([
            'nom'                 => 'sometimes|string|max:150',
            'type'                => 'sometimes|in:local,national,en_ligne',
            'statut'              => 'sometimes|in:actif,inactif,suspendu',
            'email'               => 'nullable|email',
            'telephone'           => 'nullable|string|max:20',
            'whatsapp'            => 'nullable|string|max:20',
            'adresse'             => 'nullable|string',
            'ville'               => 'nullable|string|max:100',
            'site_web'            => 'nullable|url',
            'description'         => 'nullable|string',
            'remise_cooperative'  => 'nullable|numeric|min:0|max:100',
            'delai_livraison_min' => 'nullable|integer|min:0',
            'delai_livraison_max' => 'nullable|integer|min:0',
        ]);

        $fournisseur->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur mis à jour.',
            'data'    => $this->fournisseurResource($fournisseur->fresh()->load('specialites')),
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // DELETE /api/admin/fournisseurs/{id}
    // Désactivation logique
    // ────────────────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->update(['statut' => 'inactif']);

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur désactivé.',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // POST /api/admin/fournisseurs/{id}/specialites
    // ────────────────────────────────────────────────────────────
    public function ajouterSpecialite(Request $request, int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);

        $validated = $request->validate([
            'specialite' => 'required|string|max:100',
        ]);

        $spec = FournisseurSpecialite::firstOrCreate([
            'fournisseur_id' => $id,
            'specialite'     => $validated['specialite'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spécialité ajoutée.',
            'data'    => $spec,
        ], 201);
    }

    // ── Resource helper ──────────────────────────────────────────
    private function fournisseurResource(Fournisseur $f, bool $detail = false): array
    {
        $data = [
            'id'                   => $f->id,
            'nom'                  => $f->nom,
            'type'                 => $f->type,
            'statut'               => $f->statut,
            'email'                => $f->email,
            'telephone'            => $f->telephone,
            'whatsapp'             => $f->whatsapp,
            'adresse'              => $f->adresse,
            'ville'                => $f->ville,
            'region'               => $f->region,
            'site_web'             => $f->site_web,
            'logo_url'             => $f->logo_url,
            'description'          => $f->description,
            'remise_cooperative'   => $f->remise_cooperative,
            'delai_livraison_min'  => $f->delai_livraison_min,
            'delai_livraison_max'  => $f->delai_livraison_max,
            'note_moyenne'         => $f->note_moyenne,
            'specialites'          => $f->relationLoaded('specialites')
                                        ? $f->specialites->pluck('specialite')
                                        : [],
        ];

        if ($detail) {
            $data['artisans_partenaires'] = $f->relationLoaded('artisans')
                ? $f->artisans->map(fn($a) => [
                    'id'            => $a->id,
                    'nom'           => $a->user->nom_complet,
                    'specialite'    => $a->specialite,
                    'est_principal' => $a->pivot->est_principal,
                ])
                : [];
        }

        return $data;
    }
}