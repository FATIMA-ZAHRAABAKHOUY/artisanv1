<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckArtisanActif
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user    = auth()->user();
        $artisan = $user?->artisan;

        if (! $artisan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil artisan introuvable.',
                'code'    => 'ARTISAN_NOT_FOUND',
            ], 403);
        }

        if ($artisan->statut !== 'actif') {
            return response()->json([
                'success' => false,
                'message' => "Compte artisan {$artisan->statut}.",
                'code'    => 'ARTISAN_INACTIF',
            ], 403);
        }

        if (! $artisan->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Artisan non vérifié par la coopérative.',
                'code'    => 'ARTISAN_NON_VERIFIE',
            ], 403);
        }

        return $next($request);
    }
}
