<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->when($request->filled('non_lues'), fn($q) => $q->where('is_read', false))
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $notifications->items(),
            'meta'    => [
                'total'        => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'non_lues'     => Notification::where('user_id', $request->user()->id)
                                               ->where('is_read', false)->count(),
            ],
        ]);
    }

    // GET /api/notifications/count
    public function count(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
                             ->where('is_read', false)
                             ->count();

        return response()->json(['success' => true, 'non_lues' => $count]);
    }

    // PUT /api/notifications/{id}/lire
    public function marquerLue(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
                                    ->findOrFail($id);
        $notification->marquerLue();

        return response()->json(['success' => true, 'message' => 'Notification lue.']);
    }

    // PUT /api/notifications/lire-tout
    public function marquerToutLu(Request $request): JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)
                             ->where('is_read', false)
                             ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marquée(s) comme lue(s).",
        ]);
    }
}


// ================================================================
//  app/Http/Controllers/API/SupportController.php
// ================================================================

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupportController extends Controller
{
    // GET /api/support
    public function index(Request $request): JsonResponse
    {
        $tickets = Support::where('user_id', $request->user()->id)
            ->with('livraison')
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    // POST /api/support
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'objet'       => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'colis_id'    => 'nullable|exists:livraisons,id',
        ]);

        $ticket = Support::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'statut'  => 'ouvert',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket de support créé. Nous vous répondrons dans les plus brefs délais.',
            'data'    => $ticket,
        ], 201);
    }

    // GET /api/support/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Support::where('user_id', $request->user()->id)
                         ->with('livraison')
                         ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $ticket]);
    }

    // GET /api/admin/support
    public function adminIndex(Request $request): JsonResponse
    {
        $tickets = Support::with(['user', 'livraison'])
            ->when($request->filled('statut'), fn($q) => $q->where('statut', $request->statut))
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    // PUT /api/admin/support/{id}/statut
    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $ticket = Support::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
        ]);

        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Ticket mis à jour : {$validated['statut']}.",
        ]);
    }
}