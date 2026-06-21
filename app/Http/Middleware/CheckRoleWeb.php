<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRoleWeb   // ← nom différent
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter.');
        }

        if (auth()->user()->statut !== 'actif') {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Votre compte est suspendu.');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Accès refusé.');
        }

        return $next($request);
    }
}