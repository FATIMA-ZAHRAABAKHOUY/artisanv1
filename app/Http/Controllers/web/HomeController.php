<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use App\Models\Artisan;
use App\Models\Formation;
use App\Models\Categorie;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
// ================================================================
//  HomeController
// ================================================================
class HomeController extends Controller
{
    public function index()
    {
        $produitsVedettes = Produit::with(['artisan.user', 'categorie'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->take(8)
            ->get();

        $categories = Categorie::whereNull('parent_id')
            ->withCount('produits')
            ->orderByDesc('produits_count')
            ->take(8)
            ->get();

        $formations = Formation::with(['artisan.user'])
            ->where('is_active', true)
            ->where('date_debut', '>=', now())
            ->orderBy('date_debut')
            ->take(4)
            ->get();

        $artisans = Artisan::with('user')
            ->where('is_verified', true)
            ->where('statut', 'actif')
            ->orderByDesc('note_moyenne')
            ->take(4)
            ->get();

        return view('home', compact(
            'produitsVedettes',
            'categories',
            'formations',
            'artisans'
        ));
    }
}




