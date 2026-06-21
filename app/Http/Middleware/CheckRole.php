<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        }

        if (!in_array(auth()->user()->role, $roles)) {
            return response()->json([
                'success'      => false,
                'message'      => 'Accès refusé.',
                'code'         => 'FORBIDDEN',
                'votre_role'   => auth()->user()->role,
                'roles_requis' => $roles,
            ], 403);
        }

        return $next($request);
    }
}