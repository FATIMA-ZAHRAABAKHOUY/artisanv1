<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    // ── Succès simple ─────────────────────────────────────────────
    public static function success(
        mixed  $data    = null,
        string $message = 'Succès',
        int    $status  = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    // ── Erreur ────────────────────────────────────────────────────
    public static function error(
        string $message = 'Une erreur est survenue',
        int    $status  = 400,
        mixed  $errors  = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    // ── Création (201) ────────────────────────────────────────────
    public static function created(
        mixed  $data    = null,
        string $message = 'Créé avec succès'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    // ── Paginé avec Resource ──────────────────────────────────────
    public static function paginated(
        LengthAwarePaginator $paginator,
        string $resourceClass
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data'    => $resourceClass::collection($paginator->items()),
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links'   => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
    }

    // ── Non trouvé (404) ──────────────────────────────────────────
    public static function notFound(string $message = 'Ressource introuvable'): JsonResponse
    {
        return self::error($message, 404);
    }

    // ── Accès refusé (403) ────────────────────────────────────────
    public static function forbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return self::error($message, 403);
    }

    // ── Non authentifié (401) ────────────────────────────────────
    public static function unauthorized(string $message = 'Non authentifié'): JsonResponse
    {
        return self::error($message, 401);
    }

    // ── Validation (422) ─────────────────────────────────────────
    public static function validation(array $errors, string $message = 'Données invalides'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    // ── OCL Violation (422) ───────────────────────────────────────
    public static function ocl(string $message, string $code = 'OCL_VIOLATION'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'type'    => 'ocl_constraint',
        ], 422);
    }
}