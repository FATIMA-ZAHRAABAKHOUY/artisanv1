<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Force toutes les réponses de l'API en JSON.
     * Évite les pages HTML d'erreur Laravel par défaut.
     *
     * Usage : appliqué globalement sur le groupe 'api'
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forcer Accept: application/json
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}