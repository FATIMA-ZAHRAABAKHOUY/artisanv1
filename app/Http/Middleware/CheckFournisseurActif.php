<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFournisseurActif
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'fournisseur') {
            abort(403, 'Accès réservé aux fournisseurs.');
        }

        $fournisseur = $user->fournisseur;

        if (! $fournisseur) {
            return redirect()->route('home')
                ->with('error', 'Profil fournisseur introuvable. Contactez l\'administrateur.');
        }

        if ($fournisseur->statut !== 'actif') {
            return redirect()->route('home')
                ->with('error', 'Votre compte fournisseur est en attente de validation ou inactif.');
        }

        return $next($request);
    }
}
