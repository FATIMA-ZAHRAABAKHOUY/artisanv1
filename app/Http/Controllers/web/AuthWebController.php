<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\Livreur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = auth()->user();

            // Redirection selon le rôle
            if ($user->role === 'admin' || $user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->role === 'artisan' || $user->isArtisan()) {
                return redirect()->route('artisan.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->role === 'livreur' || $user->isLivreur()) {
                return redirect()->route('livreur.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->isFournisseur()) {
                $fournisseur = $user->fournisseur;
                if (! $fournisseur || $fournisseur->statut !== 'actif') {
                    return redirect()->route('home')
                        ->with('error', 'Votre compte fournisseur est en attente de validation ou inactif.');
                }

                return redirect()->route('fournisseur.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->isFormateur()) {
                return redirect()->route('formateur.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->isApprenant()) {
                return redirect()->route('apprenant.dashboard')
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            if ($user->isClient()) {
                return redirect()->intended(route('home'))
                    ->with('success', "Bienvenue, {$user->prenom} !");
            }

            return redirect()->intended(route('home'))
                ->with('success', "Bienvenue, {$user->prenom} !");
        }

        return back()
            ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
            ->withInput($request->only('email'));
    }

    public function showRegister()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'telephone'  => 'nullable|string|max:20',
            'ville'      => 'nullable|string|max:100',
            'role'       => 'required|in:client,artisan,apprenant,livreur',
            'specialite' => 'required_if:role,artisan|nullable|string|max:150',
        ];

        $validated = $request->validate($rules);

        $user = null;

        DB::beginTransaction();

        try {
            $user = User::create([
                'nom'       => $validated['nom'],
                'prenom'    => $validated['prenom'],
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'telephone' => $validated['telephone'] ?? null,
                'ville'     => $validated['ville'] ?? null,
                'role'      => $validated['role'],
                'statut'    => 'actif',
            ]);

            if ($validated['role'] === 'artisan') {
                Artisan::create([
                    'user_id'      => $user->id,
                    'specialite'   => $validated['specialite'],
                    'statut'       => 'actif',
                    'is_verified'  => false,
                    'note_moyenne' => 0,
                ]);
            }

            if ($validated['role'] === 'livreur') {
                Livreur::create([
                    'user_id'         => $user->id,
                    'zone_couverture' => $validated['ville'] ?? null,
                    'is_disponible'   => true,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Échec inscription', [
                'role'  => $validated['role'] ?? null,
                'email' => $validated['email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Une erreur est survenue lors de l\'inscription.',
            ])->withInput();
        }

        auth()->login($user);
        $request->session()->regenerate();

        if ($user->role === 'artisan' || $user->isArtisan()) {
            return redirect()->route('artisan.dashboard')
                ->with('success', "Compte créé ! En attente de validation par l'administrateur.");
        }

        if ($user->role === 'livreur' || $user->isLivreur()) {
            return redirect()->route('livreur.dashboard')
                ->with('success', "Bienvenue {$user->prenom} ! Votre compte livreur a été créé.");
        }

        if ($user->isApprenant()) {
            return redirect()->route('apprenant.dashboard')
                ->with('success', "Bienvenue {$user->prenom} ! Votre compte apprenant a été créé.");
        }

        return redirect()->route('home')
            ->with('success', "Bienvenue {$user->prenom} ! Votre compte a été créé.");
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Vous êtes déconnecté.');
    }
}