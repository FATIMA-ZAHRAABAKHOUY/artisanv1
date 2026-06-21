<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Livraison;
use App\Models\Livreur;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LivreurController extends Controller
{
    /** Liste publique des livreurs (admin/catalogue). */
    public function index()
    {
        $livreurs = Livreur::with('user')->get();

        return view('livreurs.index', compact('livreurs'));
    }

    /** GET /livreur/dashboard */
    public function dashboard(Request $request)
    {
        $livreurId = auth()->id();

        $query = Livraison::with(['commande.client'])
            ->where('livreur_id', $livreurId);

        if ($request->filled('statut')) {
            if ($request->statut === 'en_route') {
                $query->where('statut', Livraison::STATUT_EN_TRANSIT);
            } else {
                $query->where('statut', $request->statut);
            }
        } else {
            $query->whereNotIn('statut', Livraison::STATUTS_TERMINAUX);
        }

        $livraisons = $query->latest('created_at')->paginate(15)->withQueryString();

        $disponibles = Livraison::with(['commande.client'])
            ->whereNull('livreur_id')
            ->where('statut', Livraison::STATUT_ASSIGNEE)
            ->latest('created_at')
            ->take(10)
            ->get();

        $stats = [
            'a_preparer' => Livraison::where('livreur_id', $livreurId)
                ->where('statut', Livraison::STATUT_ASSIGNEE)->count(),
            'en_route'   => Livraison::where('livreur_id', $livreurId)
                ->where('statut', Livraison::STATUT_EN_TRANSIT)->count(),
            'livrees'    => Livraison::where('livreur_id', $livreurId)
                ->where('statut', Livraison::STATUT_LIVREE)->count(),
            'retournees' => Livraison::where('livreur_id', $livreurId)
                ->where('statut', Livraison::STATUT_ECHOUEE)->count(),
            'disponibles_count' => Livraison::whereNull('livreur_id')
                ->where('statut', Livraison::STATUT_ASSIGNEE)->count(),
        ];

        return view('livreur.dashboard', compact('livraisons', 'disponibles', 'stats'));
    }

    /** GET /livreur/livraisons/{id} */
    public function show(int $id)
    {
        $livraison = Livraison::with([
            'commande.client',
            'commande.lignes.produit',
            'historique.modifiePar',
        ])
            ->where('livreur_id', auth()->id())
            ->findOrFail($id);

        return view('livreur.show', compact('livraison'));
    }

    /** PUT /livreur/livraisons/{id}/accepter */
    public function accepter(int $id)
    {
        $livraison = Livraison::with('commande')
            ->where('livreur_id', auth()->id())
            ->findOrFail($id);

        if ($livraison->statut !== Livraison::STATUT_ASSIGNEE) {
            return back()->with('error',
                'Cette livraison ne peut pas être acceptée (statut actuel : '.Livraison::statutLabel($livraison->statut).').');
        }

        $livraison->changerStatut(
            Livraison::STATUT_EN_TRANSIT,
            auth()->id(),
            'Livraison acceptée et prise en charge par le livreur.'
        );

        Notification::envoyer(
            $livraison->commande->client_id,
            'livraison_statut',
            '🚚 Votre colis est en route',
            "Votre commande #{$livraison->commande_id} a été prise en charge par le livreur.",
            ['commande_id' => $livraison->commande_id]
        );

        return back()->with('success', 'Livraison acceptée. Bon trajet !');
    }

    /** PUT /livreur/livraisons/{id}/statut */
    public function updateStatut(Request $request, int $id)
    {
        $livraison = Livraison::with('commande')
            ->where('livreur_id', auth()->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'statut'      => 'required|in:in_transit,failed',
            'commentaire' => 'nullable|string|max:300',
        ]);

        if ($livraison->estLivree()) {
            return back()->with('error',
                'Cette livraison est déjà confirmée comme livrée et ne peut plus être modifiée.');
        }

        $livraison->changerStatut(
            $validated['statut'],
            auth()->id(),
            $validated['commentaire'] ?? null
        );

        return back()->with('success', 'Statut mis à jour : '.Livraison::statutLabel($validated['statut']));
    }

    /** POST /livreur/livraisons/{id}/confirmer */
    public function confirmer(Request $request, int $id)
    {
        $livraison = Livraison::with('commande')
            ->where('livreur_id', auth()->id())
            ->findOrFail($id);

        if ($livraison->estLivree()) {
            return back()->with('error', 'Déjà confirmée comme livrée.');
        }

        if ($livraison->statut !== Livraison::STATUT_EN_TRANSIT) {
            return back()->with('error',
                'La livraison doit être en route avant confirmation.');
        }

        $validated = $request->validate([
            'commentaire' => 'nullable|string|max:300',
            'preuve'      => 'nullable|image|max:5120',
        ]);

        $update = ['date_livree' => now()];

        if ($request->hasFile('preuve') && \Illuminate\Support\Facades\Schema::hasColumn('livraisons', 'preuve_livraison_url')) {
            $update['preuve_livraison_url'] = $request->file('preuve')
                ->store("livraisons/{$id}/preuves", 'public');
        }

        $livraison->changerStatut(
            Livraison::STATUT_LIVREE,
            auth()->id(),
            $validated['commentaire'] ?? 'Livraison confirmée par le livreur.'
        );

        $livraison->update($update);

        Notification::envoyer(
            $livraison->commande->client_id,
            'livraison_statut',
            '✅ Votre commande est livrée !',
            "Votre commande #{$livraison->commande_id} a été livrée avec succès.",
            ['commande_id' => $livraison->commande_id]
        );

        return redirect()->route('livreur.dashboard')
            ->with('success', '✅ Livraison #'.$livraison->commande_id.' confirmée comme livrée !');
    }

    /** GET /livreur/profil */
    public function profil()
    {
        return view('livreur.profil');
    }

    /** PUT /livreur/profil */
    public function updateProfil(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'ville'     => 'nullable|string|max:100',
            'adresse'   => 'nullable|string|max:300',
            'avatar'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès !');
    }

    /** PUT /livreur/profil/password */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($validated['current_password'], $user->getAuthPassword())) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Mot de passe modifié avec succès !');
    }

    /** PUT /livreur/livraisons/{id}/refuser */
    public function refuser(Request $request, int $id)
    {
        $livraison = Livraison::with('commande')
            ->where('livreur_id', auth()->id())
            ->findOrFail($id);

        if ($livraison->statut !== Livraison::STATUT_ASSIGNEE) {
            return back()->with('error',
                'Vous ne pouvez refuser que les livraisons non encore acceptées.');
        }

        $validated = $request->validate([
            'motif' => 'nullable|string|max:300',
        ]);

        $motif = $validated['motif'] ?? 'Non spécifié';

        $historique = [
            'statut'      => $livraison->statut,
            'commentaire' => "Livraison refusée par le livreur. Motif : {$motif}",
        ];
        if (Schema::hasColumn('livraison_historiques', 'changed_by')) {
            $historique['changed_by'] = auth()->id();
        }
        $livraison->historique()->create($historique);

        $livraison->update(['livreur_id' => null]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::envoyer(
                $admin->id,
                'livraison_refusee',
                '⚠️ Livraison refusée',
                'Le livreur '.auth()->user()->nom_complet.
                " a refusé la livraison de la commande #{$livraison->commande_id}. ".
                "Motif : {$motif}",
                ['livraison_id' => $livraison->id, 'commande_id' => $livraison->commande_id]
            );
        }

        return redirect()->route('livreur.dashboard')
            ->with('success', 'Livraison refusée. Elle sera réassignée par l\'administrateur.');
    }

    /** PUT /livreur/livraisons/{id}/claim — prendre en charge depuis le pool non assigné */
    public function claim(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $livraison = Livraison::with('commande')
                    ->whereNull('livreur_id')
                    ->where('statut', Livraison::STATUT_ASSIGNEE)
                    ->lockForUpdate()
                    ->findOrFail($id);

                $livraison->update(['livreur_id' => auth()->id()]);

                $historique = [
                    'statut'      => Livraison::STATUT_ASSIGNEE,
                    'commentaire' => 'Livraison prise en charge par '.auth()->user()->nom_complet,
                ];
                if (Schema::hasColumn('livraison_historiques', 'changed_by')) {
                    $historique['changed_by'] = auth()->id();
                }
                $livraison->historique()->create($historique);

                if ($livraison->commande?->client_id) {
                    Notification::envoyer(
                        $livraison->commande->client_id,
                        'livraison_assignee',
                        '📦 Livreur assigné',
                        "Un livreur a été assigné à votre commande #{$livraison->commande_id}.",
                        ['commande_id' => $livraison->commande_id]
                    );
                }
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'Cette livraison n\'est plus disponible.');
        }

        return back()->with('success', 'Livraison prise en charge ! Elle apparaît maintenant dans vos livraisons actives.');
    }
}
