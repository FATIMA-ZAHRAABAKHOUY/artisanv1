<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;



// ================================================================
//  NotificationWebController
// ================================================================
class NotificationWebController extends Controller
{
    // GET /notifications
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->when($request->filled('non_lues'), fn($q) => $q->where('is_read', false))
            ->latest()
            ->paginate(20);

        // Marquer toutes comme lues si affichées
        return view('notifications.index', compact('notifications'));
    }

    // PUT /notifications/{id}/lire
    public function marquerLue(int $id)
    {
        Notification::where('user_id', auth()->id())
            ->findOrFail($id)
            ->update(['is_read' => true]);

        return back()->with('success', 'Notification marquée comme lue.');
    }

    // POST /notifications/lire-tout
    public function marquerToutLu()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', "{$count} notification(s) marquée(s) comme lue(s).");
    }

    // GET /notifications/count-ajax (pour le badge JS)
    public function countAjax()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}

