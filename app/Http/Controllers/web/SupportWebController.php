<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SupportWebController extends Controller
{
    // GET /support
    public function index()
    {
        $tickets = Support::where('user_id', auth()->id())
            ->with('livraison')
            ->latest()
            ->paginate(10);

        return view('support.index', compact('tickets'));
    }

    // POST /support
    public function store(Request $request)
    {
        $validated = $request->validate([
            'objet'       => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'colis_id'    => 'nullable|exists:livraisons,id',
        ]);

        Support::create([
            ...$validated,
            'user_id' => auth()->id(),
            'statut'  => 'ouvert',
        ]);

        // Notifier l'admin (optionnel)
        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            Notification::envoyer(
                $admin->id,
                'nouveau_ticket',
                '🎫 Nouveau ticket support',
                auth()->user()->nom_complet . " a ouvert un ticket : {$validated['objet']}",
                ['user_id' => auth()->id()]
            );
        }

        return back()->with('success',
            'Votre ticket a été créé. Nous vous répondrons dans les 24h ouvrées.');
    }

    // GET /support/{id}
    public function show(int $id)
    {
        $ticket = Support::where('user_id', auth()->id())
            ->with('livraison')
            ->findOrFail($id);

        return view('support.show', compact('ticket'));
    }
}
