<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware global pour l'API (Force le format JSON)
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Alias de Middleware
        $middleware->alias([
            // API
            'role'            => \App\Http\Middleware\CheckRole::class,
            'artisan.actif'   => \App\Http\Middleware\CheckArtisanActif::class,

            // Web
            'role.web'        => \App\Http\Middleware\CheckRoleWeb::class,
            'artisan.actif.web' => \App\Http\Middleware\CheckArtisanActifWeb::class,
            'livreur.actif'   => \App\Http\Middleware\CheckLivreurActif::class,
            'fournisseur.actif' => \App\Http\Middleware\CheckFournisseurActif::class,
            'formateur.actif'   => \App\Http\Middleware\CheckFormateurActif::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion globale de l'exception d'authentification pour renvoyer du JSON
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Optionnel : vérifier si la requête demande du JSON pour ne pas casser le routage Web
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié.',
                    'code'    => 'UNAUTHENTICATED'
                ], 401);
            }
        });
    })
    ->create();