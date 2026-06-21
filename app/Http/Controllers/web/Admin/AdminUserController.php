<?php

namespace App\Http\Controllers\web\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('artisan')
            ->when($request->filled('role'),   fn($q) => $q->where('role',   $request->role))
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('q'), fn($q) =>
                $q->where(fn($s) =>
                    $s->where('nom',    'ilike', "%{$request->q}%")
                      ->orWhere('prenom', 'ilike', "%{$request->q}%")
                      ->orWhere('email',  'ilike', "%{$request->q}%")
                )
            )
            ->latest()
            ->paginate(20);
 
        return view('admin.users', compact('users'));
    }
 
    public function show(int $id)
    {
        $user = User::with(['artisan', 'commandes'])->findOrFail($id);
        return view('admin.user_show', compact('user'));
    }
 
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'ville'     => 'nullable|string|max:100',
            'role'      => 'sometimes|in:client,artisan,admin,livreur,apprenant',
        ]));
        return back()->with('success', 'Utilisateur mis à jour.');
    }
 
    public function suspendre(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['statut' => 'suspendu']);
        if ($user->artisan) $user->artisan->update(['statut' => 'suspendu']);
        return back()->with('success', "{$user->nom_complet} suspendu.");
    }
 
    public function activer(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['statut' => 'actif']);
        if ($user->artisan && $user->artisan->statut === 'suspendu') {
            $user->artisan->update(['statut' => 'actif']);
        }
        return back()->with('success', "{$user->nom_complet} activé.");
    }
 
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de supprimer un administrateur.');
        }
        $user->update(['statut' => 'inactif']);
        return back()->with('success', 'Compte désactivé.');
    }
}
 
 
