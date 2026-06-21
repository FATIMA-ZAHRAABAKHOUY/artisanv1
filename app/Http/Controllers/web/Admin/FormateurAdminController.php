<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\Formateur;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class FormateurAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Formateur::with(['user', 'artisan.user'])
            ->withCount('formations');

        if ($request->filled('type')) {
            $query->where('est_externe', $request->type === 'externe');
        }
        if ($request->filled('disponible')) {
            $query->where('is_disponible', $request->disponible === '1');
        }
        if ($request->filled('q')) {
            $query->where('specialite', 'ilike', '%' . $request->q . '%');
        }

        $formateurs = $query->latest()->paginate(15);

        return view('admin.formateurs.index', compact('formateurs'));
    }

    public function create()
    {
        $artisansDisponibles = Artisan::with('user')
            ->where('is_verified', true)
            ->where('statut', 'actif')
            ->whereDoesntHave('formateur')
            ->get();

        return view('admin.formateurs.form', compact('artisansDisponibles'));
    }

    public function store(Request $request)
    {
        $rules = [
            'est_externe'       => 'required|boolean',
            'biographie'        => 'nullable|string',
            'specialite'        => 'required|string|max:150',
            'diplomes'          => 'nullable|string',
            'langues'           => 'nullable|string|max:200',
            'experience_annees' => 'nullable|integer|min:0',
            'is_disponible'     => 'nullable|boolean',
        ];

        if ($request->boolean('est_externe')) {
            $rules['organisme']     = 'nullable|string|max:200';
            $rules['tarif_journee'] = 'nullable|numeric|min:0';
            $rules['creer_acces']   = 'nullable|boolean';

            if ($request->boolean('creer_acces')) {
                $rules['nom']      = 'required|string|max:100';
                $rules['prenom']   = 'required|string|max:100';
                $rules['email']    = 'required|email|unique:users,email';
                $rules['password'] = 'required|string|min:8';
            }
        } else {
            $rules['artisan_id'] = 'required|exists:artisans,id';
        }

        $validated = $request->validate($rules);

        if ($request->boolean('est_externe')
            && ! $request->boolean('creer_acces')
            && empty($validated['organisme'])) {
            return back()->withErrors([
                'organisme' => 'Renseignez un organisme ou cochez « Créer un accès de connexion ».',
            ])->withInput();
        }

        $userId = null;

        if ($request->boolean('est_externe') && $request->boolean('creer_acces')) {
            $user = User::create([
                'nom'      => $validated['nom'],
                'prenom'   => $validated['prenom'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
                'role'     => 'formateur',
                'statut'   => 'actif',
            ]);
            $userId = $user->id;

            Notification::envoyer(
                $user->id,
                'compte_cree',
                '🎓 Compte formateur créé',
                'Bienvenue ! Vous pouvez désormais consulter vos formations assignées depuis votre espace formateur.',
                []
            );
        }

        $formateurData = collect($validated)
            ->except(['creer_acces', 'nom', 'prenom', 'email', 'password', 'artisan_id'])
            ->toArray();

        $formateurData['is_disponible'] = $request->boolean('is_disponible', true);
        $formateurData['est_externe']   = $request->boolean('est_externe');

        if ($request->boolean('est_externe')) {
            $formateurData['user_id']    = $userId;
            $formateurData['artisan_id'] = null;
        } else {
            $artisan = Artisan::with('user')->findOrFail($validated['artisan_id']);

            if (! $artisan->is_verified || $artisan->statut !== 'actif') {
                return back()->withErrors([
                    'artisan_id' => 'L\'artisan sélectionné doit être vérifié et actif.',
                ])->withInput();
            }

            if ($artisan->formateur) {
                return back()->withErrors([
                    'artisan_id' => 'Cet artisan est déjà enregistré comme formateur.',
                ])->withInput();
            }

            $formateurData['artisan_id'] = $artisan->id;
            $formateurData['user_id']    = $artisan->user_id;
            $formateurData['est_externe'] = false;
            $formateurData['organisme']  = null;
        }

        $formateur = Formateur::create($formateurData);

        return redirect()->route('admin.formateurs.index')
            ->with('success', "Formateur « {$formateur->specialite} » créé avec succès.");
    }

    public function edit(int $id)
    {
        $formateur = Formateur::with(['user', 'artisan.user'])->findOrFail($id);

        return view('admin.formateurs.form', compact('formateur'));
    }

    public function update(Request $request, int $id)
    {
        $formateur = Formateur::findOrFail($id);

        $validated = $request->validate([
            'biographie'        => 'nullable|string',
            'specialite'        => 'required|string|max:150',
            'diplomes'          => 'nullable|string',
            'langues'           => 'nullable|string|max:200',
            'experience_annees' => 'nullable|integer|min:0',
            'organisme'         => 'nullable|string|max:200',
            'tarif_journee'     => 'nullable|numeric|min:0',
            'is_disponible'     => 'nullable|boolean',
        ]);

        $validated['is_disponible'] = $request->boolean('is_disponible');

        $formateur->update($validated);

        return back()->with('success', 'Formateur mis à jour.');
    }

    public function destroy(int $id)
    {
        $formateur = Formateur::findOrFail($id);

        if ($formateur->formations()->exists()) {
            return back()->with('error',
                'Impossible de supprimer : ce formateur est assigné à des formations. ' .
                'Désactivez-le plutôt (is_disponible = false).');
        }

        $formateur->delete();

        return back()->with('success', 'Formateur supprimé.');
    }

    public function toggleDisponible(int $id)
    {
        $formateur = Formateur::findOrFail($id);
        $formateur->update(['is_disponible' => ! $formateur->is_disponible]);

        return back()->with('success',
            'Formateur ' . ($formateur->is_disponible ? 'rendu disponible' : 'rendu indisponible') . '.');
    }
}
