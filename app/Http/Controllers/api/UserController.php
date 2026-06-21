<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    // GET /api/admin/users
    public function index(Request $request): JsonResponse
    {
        $users = User::with('artisan')
            ->when($request->filled('role'),   fn($q) => $q->where('role', $request->role))
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->when($request->filled('q'), fn($q) =>
                $q->where(fn($s) =>
                    $s->where('nom',   'ilike', "%{$request->q}%")
                      ->orWhere('prenom', 'ilike', "%{$request->q}%")
                      ->orWhere('email',  'ilike', "%{$request->q}%")
                )
            )
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $users->map(fn($u) => $this->userResource($u)),
            'meta'    => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    // GET /api/admin/users/{id}
    public function show(int $id): JsonResponse
    {
        $user = User::with(['artisan', 'formateur', 'commandes'])
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->userResource($user, true),
        ]);
    }

    // PUT /api/admin/users/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'ville'     => 'nullable|string|max:100',
            'role'      => 'sometimes|in:client,artisan,admin,livreur,apprenant',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour.',
            'data'    => $this->userResource($user->fresh()),
        ]);
    }

    // PUT /api/admin/users/{id}/suspendre
    public function suspendre(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['statut' => 'suspendu']);

        // Suspendre le profil artisan si applicable
        if ($user->artisan) {
            $user->artisan->update(['statut' => 'suspendu']);
        }

        return response()->json([
            'success' => true,
            'message' => "Compte de {$user->nom_complet} suspendu.",
        ]);
    }

    // PUT /api/admin/users/{id}/activer
    public function activer(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['statut' => 'actif']);

        if ($user->artisan && $user->artisan->statut === 'suspendu') {
            $user->artisan->update(['statut' => 'actif']);
        }

        return response()->json([
            'success' => true,
            'message' => "Compte de {$user->nom_complet} activé.",
        ]);
    }

    // DELETE /api/admin/users/{id}
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Ne pas supprimer un admin
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un administrateur.',
            ], 422);
        }

        $user->update(['statut' => 'inactif']);

        return response()->json([
            'success' => true,
            'message' => 'Compte désactivé.',
        ]);
    }

    private function userResource(User $u, bool $detail = false): array
    {
        $data = [
            'id'         => $u->id,
            'nom_complet'=> $u->nom_complet,
            'nom'        => $u->nom,
            'prenom'     => $u->prenom,
            'email'      => $u->email,
            'telephone'  => $u->telephone,
            'ville'      => $u->ville,
            'role'       => $u->role,
            'statut'     => $u->statut,
            'avatar_url' => $u->avatar_url,
            'created_at' => $u->created_at?->format('d/m/Y'),
            'artisan'    => $u->artisan ? [
                'id'           => $u->artisan->id,
                'specialite'   => $u->artisan->specialite,
                'statut'       => $u->artisan->statut,
                'is_verified'  => $u->artisan->is_verified,
                'note_moyenne' => $u->artisan->note_moyenne,
                'date_adhesion'=> $u->artisan->date_adhesion?->format('d/m/Y'),
            ] : null,
        ];

        if ($detail) {
            $data['nb_commandes'] = $u->commandes()->count();
            $data['total_depense'] = $u->commandes()
                ->where('statut', 'delivered')
                ->sum('total_ttc');
        }

        return $data;
    }
}
