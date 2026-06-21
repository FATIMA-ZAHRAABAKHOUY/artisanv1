<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Artisan;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Livraison;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Nombre d'artisans en attente de vérification
        $enAttente = Artisan::where('is_verified', false)->count();

        // 2. Nombre total de clients
        $clientsCount = User::where('role', 'client')->count();

        // 3. Nombre d'artisans déjà vérifiés
        $artisansVérifiés = Artisan::where('is_verified', true)->count();

        // 4. Nombre de commandes livrées
        $commandesLivrees = Commande::where('statut', 'delivered')->count();

        // 5. Calcul du Chiffre d'Affaires (Somme des paiements réussis)
        $chiffreAffaires = Paiement::where('statut', 'paid')->sum('montant');

        // 6. Les 6 dernières commandes avec les infos du client associé
        $commandesRecentes = Commande::with('client')->latest()->take(6)->get();

        // 7. Liste des artisans en attente (affichage max 4)
        $listeAttentes = Artisan::with('user')->where('is_verified', false)->take(4)->get();

        // 8. Nombre de livraisons en attente d'assignation
        $sansLivreur = Livraison::sansLivreurActives()->count();

        // On envoie toutes ces variables à la vue Blade
        return view('admin.dashboard', compact(
            'enAttente',
            'clientsCount',
            'artisansVérifiés',
            'commandesLivrees',
            'chiffreAffaires',
            'commandesRecentes',
            'listeAttentes',
            'sansLivreur'
        ));
    }
}