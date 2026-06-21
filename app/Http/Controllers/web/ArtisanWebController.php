<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\InscriptionFormation;
use App\Models\Artisan;
use App\Models\Notification;
use Illuminate\Http\Request;

class ArtisanWebController extends Controller
{
    // GET /artisans
    public function index(Request $request)
    {
        $artisans = Artisan::with('user')
            ->where('is_verified', true)
            ->where('statut', 'actif')
            ->when($request->filled('specialite'), fn($q) =>
                $q->where('specialite', 'ilike', "%{$request->specialite}%")
            )
            ->when($request->filled('region'), fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('ville', 'ilike', "%{$request->region}%")
                )
            )
            ->withCount('produits')
            ->orderByDesc('note_moyenne')
            ->paginate(12);
 
        return view('artisans.index', compact('artisans'));
    }
 
    // GET /artisans/{id}
    public function show(int $id)
    {
        $artisan = Artisan::with(['user'])
            ->where('is_verified', true)
            ->findOrFail($id);
 
        $produits = $artisan->produits()
            ->with('categorie')
            ->where('is_active', true)
            ->latest()
            ->paginate(9);
 
        return view('artisans.show', compact('artisan', 'produits'));
    }
    
}
 
 