<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckArtisanActifWeb
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user    = auth()->user();
        $artisan = $user?->artisan;

        if (! $artisan) {
            abort(403, 'Profil artisan introuvable.');
        }

        if ($artisan->statut !== 'actif') {
            abort(403, "Compte artisan {$artisan->statut}.");
        }

        if (! $artisan->is_verified) {
            abort(403, 'Artisan non vérifié par la coopérative.');
        }

        return $next($request);
    }
}
