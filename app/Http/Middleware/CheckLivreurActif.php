<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLivreurActif
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'livreur') {
            abort(403, 'Accès réservé aux livreurs.');
        }

        if ($user->statut !== 'actif') {
            auth()->logout();

            return redirect()->route('login')
                ->with('error', 'Votre compte livreur est suspendu.');
        }

        return $next($request);
    }
}
