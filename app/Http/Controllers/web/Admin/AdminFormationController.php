<?php

namespace App\Http\Controllers\Web\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{User, Artisan, Produit, Commande, Livraison, Formation,
                Fournisseur, Categorie, Support, Paiement, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class AdminFormationController extends Controller
{
    public function index(Request $request)
    {
        $formations = Formation::with('artisan.user')
            ->withCount(['inscriptions',
                'inscriptions as en_cours_count' => fn($q) =>
                    $q->where('statut_inscription', 'en_cours')
            ])
            ->when($request->filled('actif'), fn($q) =>
                $q->where('is_active', $request->actif)
            )
            ->latest()
            ->paginate(15);
 
        return view('admin.formations', compact('formations'));
    }
}
 
 
