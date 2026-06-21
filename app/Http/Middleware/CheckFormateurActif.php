<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFormateurActif
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (! $user || ! $user->isFormateur()) {
            abort(403, 'Accès réservé aux formateurs.');
        }

        $formateur = $user->formateur;

        if (! $formateur) {
            return redirect()->route('home')
                ->with('error', 'Profil formateur introuvable. Contactez l\'administrateur.');
        }

        if (! $formateur->is_disponible) {
            return redirect()->route('home')
                ->with('error', 'Votre compte formateur est actuellement marqué indisponible.');
        }

        return $next($request);
    }
}
