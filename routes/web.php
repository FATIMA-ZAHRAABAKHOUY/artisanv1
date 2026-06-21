<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apprenti\DashboardController as ApprentiDashboardController;
use App\Http\Controllers\Web\{
    HomeController,
    AuthWebController,
    CatalogueController,
    PanierController,
    CheckoutController,
    CommandeWebController,
    FormationWebController,
    ArtisanWebController,
    ArtisanEspaceController,
    ProfileController,
    NotificationWebController,
    SupportWebController,
    InscriptionFormationWebController,
    LivreurController,
    ApprenantController,
    FournisseurController,
    FournisseurEspaceController,
    FormateurEspaceController,
};
use App\Http\Controllers\Web\Admin\{
    DashboardController,
    AdminDashboardController,
    AdminUserController,
    AdminArtisanController,
    AdminProduitController,
    AdminCommandeController,
    AdminLivraisonController,
    AdminFormationController,
    FournisseurAdminController,
    FormateurAdminController,
    AdminCategorieController,
    AdminSupportController,
};

// ════════════════════════════════════════════════════════════════
// 🔓 ROUTES PUBLIQUES
// ════════════════════════════════════════════════════════════════

// Accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Authentification ─────────────────────────────────────────────
Route::get('/connexion',    [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/connexion',   [AuthWebController::class, 'login']);
Route::get('/inscription',  [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/inscription', [AuthWebController::class, 'register']);
Route::post('/deconnexion', [AuthWebController::class, 'logout'])->name('logout');

// Mot de passe oublié
Route::get('/mot-de-passe-oublie', function () {
    return view('auth.forgot-password');
})->name('password.request');

// ── Catalogue public ─────────────────────────────────────────────
Route::prefix('catalogue')->name('catalogue.')->group(function () {
    Route::get('/',                 [CatalogueController::class, 'index'])->name('index');
    Route::get('/categorie/{slug}', [CatalogueController::class, 'categorie'])->name('categorie');
    Route::get('/{id}',             [CatalogueController::class, 'show'])->name('show')->whereNumber('id');
});

// ── Artisans public ──────────────────────────────────────────────
Route::prefix('artisans')->name('artisans.')->group(function () {
    Route::get('/',     [ArtisanWebController::class, 'index'])->name('index');
    Route::get('/{id}', [ArtisanWebController::class, 'show'])->name('show')->whereNumber('id');
});

// ── Formations publiques ─────────────────────────────────────────
Route::prefix('formations')->name('formations.')->group(function () {
    Route::get('/',     [FormationWebController::class, 'index'])->name('index');
    Route::get('/{id}', [FormationWebController::class, 'show'])->name('show')->whereNumber('id');
});

// ── Fournisseurs publics ─────────────────────────────────────────
Route::prefix('fournisseurs')->name('fournisseurs.')->group(function () {
    Route::get('/', [FournisseurController::class, 'index'])->name('index');
    Route::get('/{id}', [FournisseurController::class, 'show'])->name('show')->whereNumber('id');
});


// ════════════════════════════════════════════════════════════════
// 🔒 ROUTES AUTHENTIFIÉES (Utilisateurs connectés)
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Profil utilisateur ───────────────────────────────────────────
    Route::get('/mon-profil', [ProfileController::class, 'show'])->name('profile');
    Route::put('/mon-profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/mon-profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Fournisseurs — suggestions formation + tracking ──────────────
    Route::get('/formations/{id}/fournisseurs', [FournisseurController::class, 'suggestionsPourFormation'])
        ->name('formations.fournisseurs')
        ->whereNumber('id');
    Route::post('/fournisseurs/{id}/click', [FournisseurController::class, 'trackClick'])
        ->name('fournisseurs.click')
        ->whereNumber('id');

    // ── Panier de Commande ───────────────────────────────────────────
    Route::prefix('panier')->name('panier.')->group(function () {
        Route::get('/',       [PanierController::class, 'index'])->name('index');
        Route::post('/{id}',  [PanierController::class, 'ajouter'])->name('ajouter')->whereNumber('id');
        Route::put('/{id}',   [PanierController::class, 'update'])->name('update')->whereNumber('id');
        Route::delete('/{id}',[PanierController::class, 'supprimer'])->name('supprimer')->whereNumber('id');
        Route::delete('/',    [PanierController::class, 'vider'])->name('vider');
    });


    // ── Checkout tunnel de vente ─────────────────────────────────────
    Route::prefix('commande')->name('checkout.')->group(function () {
        Route::get('/',       [CheckoutController::class, 'index'])->name('index');
        Route::post('/',      [CheckoutController::class, 'store'])->name('store');
    });

    // ── Commandes Client / Apprenant ─────────────────────────────────
    Route::prefix('commandes')->name('commandes.')->group(function () {
        Route::get('/',                   [CommandeWebController::class, 'index'])->name('index');
        Route::get('/{id}',              [CommandeWebController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/confirmation', [CommandeWebController::class, 'confirmation'])->name('confirmation')->whereNumber('id');
        Route::post('/{id}/annuler',     [CommandeWebController::class, 'annuler'])->name('annuler')->whereNumber('id');
        Route::post('/{id}/avis',        [CatalogueController::class, 'ajouterAvis'])->name('avis')->whereNumber('id');
    });

    // Avis produit depuis le catalogue
    Route::post('/catalogue/{id}/avis', [CatalogueController::class, 'ajouterAvis'])->name('catalogue.avis')->whereNumber('id');

    // ── Formations & Inscriptions ────────────────────────────────────
    Route::prefix('formations')->name('formations.')->group(function () {
        Route::get('/mes-inscriptions', [FormationWebController::class, 'mesInscriptions'])->name('mes-inscriptions');
        Route::get('/{id}/ressources', [FormationWebController::class, 'ressources'])->name('ressources')->whereNumber('id');

        Route::middleware(['role.web:apprenant'])->group(function () {
            Route::post('/{id}/inscrire', [FormationWebController::class, 'inscrire'])->name('inscrire')->whereNumber('id');
            Route::put('/inscriptions/{id}/abandonner', [FormationWebController::class, 'abandonner'])->name('abandonner')->whereNumber('id');
        });
    });

    // ── Notifications Système ────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',          [NotificationWebController::class, 'index'])->name('index');
        Route::put('/{id}/lire', [NotificationWebController::class, 'marquerLue'])->name('lire')->whereNumber('id');
        Route::post('/lire-tout',[NotificationWebController::class, 'marquerToutLu'])->name('lire-tout');
    });

    // ── Support client ───────────────────────────────────────────────
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/',      [SupportWebController::class, 'index'])->name('index');
        Route::post('/',     [SupportWebController::class, 'store'])->name('store');
        Route::get('/{id}',  [SupportWebController::class, 'show'])->name('show')->whereNumber('id');
    });

    // ── Index général des livreurs pour les administrateurs/artisans ──
    Route::get('/livreurs', [LivreurController::class, 'index'])->name('livreurs.index');

    // ════════════════════════════════════════════════════════════
    // 🎓 ESPACE APPRENTI / APPRENANT
    // ════════════════════════════════════════════════════════════
    Route::middleware(['role.web:apprenant,admin'])
        ->prefix('apprenti')
        ->name('apprenti.')
        ->group(function () {
            Route::get('/dashboard', [ApprentiDashboardController::class, 'index'])->name('dashboard');
        });

    Route::redirect('/apprenant/dashboard', '/apprenti/dashboard')->name('apprenant.dashboard');

    // ════════════════════════════════════════════════════════════
    // 🛡️ DASHBOARD ADMIN (Blade + layouts.app)
    // ════════════════════════════════════════════════════════════
    Route::middleware(['role.web:admin'])
        ->group(function () {
            Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        });

    // ════════════════════════════════════════════════════════════
    // 👨‍🎨 ESPACE ARTISAN
    // ════════════════════════════════════════════════════════════
    Route::middleware(['role.web:artisan'])
        ->prefix('artisan')
        ->name('artisan.')
        ->group(function () {
            Route::get('/dashboard', [ArtisanEspaceController::class, 'dashboard'])->name('dashboard');
        });

    Route::middleware(['role.web:artisan', 'artisan.actif.web'])
        ->prefix('artisan')
        ->name('artisan.')
        ->group(function () {
            Route::get('/commandes', [ArtisanEspaceController::class, 'commandes'])->name('commandes');

            Route::get('/produits', [ArtisanEspaceController::class, 'produits'])->name('produits');
            Route::prefix('produits')->name('produits.')->group(function () {
                Route::get('/creer',     [ArtisanEspaceController::class, 'createProduit'])->name('create');
                Route::post('/',         [ArtisanEspaceController::class, 'storeProduit'])->name('store');
                Route::get('/{id}/edit', [ArtisanEspaceController::class, 'editProduit'])->name('edit')->whereNumber('id');
                Route::put('/{id}',      [ArtisanEspaceController::class, 'updateProduit'])->name('update')->whereNumber('id');
                Route::delete('/{id}',   [ArtisanEspaceController::class, 'destroyProduit'])->name('destroy')->whereNumber('id');
            });

            Route::get('/formations', [ArtisanEspaceController::class, 'formations'])->name('formations');
            Route::prefix('formations')->name('formations.')->group(function () {
                Route::get('/creer',     [ArtisanEspaceController::class, 'createFormation'])->name('create');
                Route::post('/',         [ArtisanEspaceController::class, 'storeFormation'])->name('store');
                Route::get('/{id}/edit', [ArtisanEspaceController::class, 'editFormation'])->name('edit')->whereNumber('id');
                Route::put('/{id}',      [ArtisanEspaceController::class, 'updateFormation'])->name('update')->whereNumber('id');
                Route::get('/{id}/contenu', [ArtisanEspaceController::class, 'gererContenu'])->name('contenu')->whereNumber('id');

                Route::post('/{id}/etapes', [ArtisanEspaceController::class, 'storeEtape'])->name('etapes.store')->whereNumber('id');
                Route::put('/{id}/etapes/{etapeId}', [ArtisanEspaceController::class, 'updateEtape'])->name('etapes.update')->whereNumber(['id', 'etapeId']);
                Route::delete('/{id}/etapes/{etapeId}', [ArtisanEspaceController::class, 'destroyEtape'])->name('etapes.destroy')->whereNumber(['id', 'etapeId']);

                Route::post('/{id}/materiaux', [ArtisanEspaceController::class, 'storeMateriau'])->name('materiaux.store')->whereNumber('id');
                Route::put('/{id}/materiaux/{materiauId}', [ArtisanEspaceController::class, 'updateMateriau'])->name('materiaux.update')->whereNumber(['id', 'materiauId']);
                Route::delete('/{id}/materiaux/{materiauId}', [ArtisanEspaceController::class, 'destroyMateriau'])->name('materiaux.destroy')->whereNumber(['id', 'materiauId']);

                Route::post('/{id}/outils', [ArtisanEspaceController::class, 'storeOutil'])->name('outils.store')->whereNumber('id');
                Route::put('/{id}/outils/{outilId}', [ArtisanEspaceController::class, 'updateOutil'])->name('outils.update')->whereNumber(['id', 'outilId']);
                Route::delete('/{id}/outils/{outilId}', [ArtisanEspaceController::class, 'destroyOutil'])->name('outils.destroy')->whereNumber(['id', 'outilId']);

                Route::post('/{id}/ressources', [ArtisanEspaceController::class, 'storeRessource'])->name('ressources.store')->whereNumber('id');
                Route::put('/{id}/ressources/{ressourceId}', [ArtisanEspaceController::class, 'updateRessource'])->name('ressources.update')->whereNumber(['id', 'ressourceId']);
                Route::delete('/{id}/ressources/{ressourceId}', [ArtisanEspaceController::class, 'destroyRessource'])->name('ressources.destroy')->whereNumber(['id', 'ressourceId']);

                // Suivi des inscrits par l'artisan aux formations
                Route::get('/{id}/inscrits',                 [InscriptionFormationWebController::class, 'inscrits'])->name('inscrits')->whereNumber('id');
                Route::put('/inscriptions/{id}/progression', [InscriptionFormationWebController::class, 'updateProgression'])->name('progression')->whereNumber('id');
                Route::post('/inscriptions/{id}/certificat', [InscriptionFormationWebController::class, 'delivrerCertificat'])->name('certificat')->whereNumber('id');
            });
    });


    // ════════════════════════════════════════════════════════════
    // 🚚 ESPACE LIVREUR
    // ════════════════════════════════════════════════════════════
    Route::middleware(['auth', 'role.web:livreur', 'livreur.actif'])
        ->prefix('livreur')
        ->name('livreur.')
        ->group(function () {
            Route::get('/dashboard', [LivreurController::class, 'dashboard'])->name('dashboard');
            Route::get('/profil', [LivreurController::class, 'profil'])->name('profil');
            Route::put('/profil', [LivreurController::class, 'updateProfil'])->name('profil.update');
            Route::put('/profil/password', [LivreurController::class, 'updatePassword'])->name('profil.password');
            Route::get('/livraisons/{id}', [LivreurController::class, 'show'])->name('livraisons.show')->whereNumber('id');
            Route::put('/livraisons/{id}/claim', [LivreurController::class, 'claim'])->name('livraisons.claim')->whereNumber('id');
            Route::put('/livraisons/{id}/accepter', [LivreurController::class, 'accepter'])->name('livraisons.accepter')->whereNumber('id');
            Route::put('/livraisons/{id}/refuser', [LivreurController::class, 'refuser'])->name('livraisons.refuser')->whereNumber('id');
            Route::put('/livraisons/{id}/statut', [LivreurController::class, 'updateStatut'])->name('livraisons.statut')->whereNumber('id');
            Route::post('/livraisons/{id}/confirmer', [LivreurController::class, 'confirmer'])->name('livraisons.confirmer')->whereNumber('id');
        });

    // ════════════════════════════════════════════════════════════
    // 🏭 ESPACE FOURNISSEUR
    // ════════════════════════════════════════════════════════════
    Route::middleware(['auth', 'role.web:fournisseur', 'fournisseur.actif'])
        ->prefix('fournisseur')
        ->name('fournisseur.')
        ->group(function () {
            Route::get('/dashboard', [FournisseurEspaceController::class, 'dashboard'])->name('dashboard');
            Route::get('/produits', [FournisseurEspaceController::class, 'produits'])->name('produits');
            Route::put('/produits/materiau/{id}', [FournisseurEspaceController::class, 'updateMateriau'])->name('produits.materiau.update')->whereNumber('id');
            Route::put('/produits/outil/{id}', [FournisseurEspaceController::class, 'updateOutil'])->name('produits.outil.update')->whereNumber('id');
            Route::get('/profil', [FournisseurEspaceController::class, 'profil'])->name('profil');
            Route::put('/profil', [FournisseurEspaceController::class, 'updateProfil'])->name('profil.update');
        });

    // ════════════════════════════════════════════════════════════
    // 🎓 ESPACE FORMATEUR (externes avec accès login)
    // ════════════════════════════════════════════════════════════
    Route::middleware(['auth', 'role.web:formateur', 'formateur.actif'])
        ->prefix('formateur')
        ->name('formateur.')
        ->group(function () {
            Route::get('/dashboard', [FormateurEspaceController::class, 'dashboard'])->name('dashboard');
            Route::get('/profil', [FormateurEspaceController::class, 'profil'])->name('profil');
            Route::put('/profil', [FormateurEspaceController::class, 'updateProfil'])->name('profil.update');
        });

    // ════════════════════════════════════════════════════════════
    // 🛡️ ESPACE ADMIN BLADE (préfixe /gestion — Filament reste sur /admin)
    // ════════════════════════════════════════════════════════════
    Route::middleware(['role.web:admin'])
        ->prefix('gestion')
        ->name('admin.')
        ->group(function () {

            Route::redirect('/dashboard', '/admin/dashboard');
            Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('dashboard.charts');
            Route::redirect('/', '/admin/dashboard');

            Route::get('/utilisateurs', [AdminUserController::class, 'index'])->name('users');
            Route::get('/utilisateurs/{id}', [AdminUserController::class, 'show'])->name('users.show')->whereNumber('id');
            Route::put('/utilisateurs/{id}', [AdminUserController::class, 'update'])->name('users.update')->whereNumber('id');
            Route::post('/utilisateurs/{id}/suspendre', [AdminUserController::class, 'suspendre'])->name('users.suspendre')->whereNumber('id');
            Route::post('/utilisateurs/{id}/activer', [AdminUserController::class, 'activer'])->name('users.activer')->whereNumber('id');
            Route::delete('/utilisateurs/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy')->whereNumber('id');

            Route::get('/artisans', [AdminArtisanController::class, 'index'])->name('artisans');
            Route::post('/artisans/{id}/valider', [AdminArtisanController::class, 'valider'])->name('artisans.valider')->whereNumber('id');
            Route::post('/artisans/{id}/suspendre', [AdminArtisanController::class, 'suspendre'])->name('artisans.suspendre')->whereNumber('id');

            Route::get('/produits', [AdminProduitController::class, 'index'])->name('produits');
            Route::post('/produits/{id}/toggle', [AdminProduitController::class, 'toggle'])->name('produits.toggle')->whereNumber('id');

            Route::get('/commandes', [AdminCommandeController::class, 'index'])->name('commandes');
            Route::get('/commandes/{id}', [AdminCommandeController::class, 'show'])->name('commandes.show')->whereNumber('id');
            Route::put('/commandes/{id}/statut', [AdminCommandeController::class, 'updateStatut'])->name('commandes.statut')->whereNumber('id');

            Route::get('/livraisons', [AdminLivraisonController::class, 'index'])->name('livraisons');
            Route::get('/livraisons/{id}/assigner', [AdminLivraisonController::class, 'assignerForm'])->name('livraisons.assigner')->whereNumber('id');
            Route::post('/livraisons/{id}/assigner', [AdminLivraisonController::class, 'assigner'])->name('livraisons.assigner.store')->whereNumber('id');

            Route::get('/formations', [AdminFormationController::class, 'index'])->name('formations');

            Route::prefix('fournisseurs')->name('fournisseurs.')->group(function () {
                Route::get('/', [FournisseurAdminController::class, 'index'])->name('index');
                Route::get('/create', [FournisseurAdminController::class, 'create'])->name('create');
                Route::post('/', [FournisseurAdminController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [FournisseurAdminController::class, 'edit'])->name('edit')->whereNumber('id');
                Route::put('/{id}', [FournisseurAdminController::class, 'update'])->name('update')->whereNumber('id');
                Route::put('/{id}/activer', [FournisseurAdminController::class, 'activer'])->name('activer')->whereNumber('id');
                Route::delete('/{id}', [FournisseurAdminController::class, 'destroy'])->name('destroy')->whereNumber('id');
                Route::get('/{id}/produits', [FournisseurAdminController::class, 'produits'])->name('produits')->whereNumber('id');
                Route::post('/{id}/materiaux', [FournisseurAdminController::class, 'ajouterMateriau'])->name('materiaux.store')->whereNumber('id');
                Route::post('/{id}/outils', [FournisseurAdminController::class, 'ajouterOutil'])->name('outils.store')->whereNumber('id');
            });
            Route::redirect('/fournisseurs/creer', '/gestion/fournisseurs/create');

            Route::prefix('formateurs')->name('formateurs.')->group(function () {
                Route::get('/', [FormateurAdminController::class, 'index'])->name('index');
                Route::get('/create', [FormateurAdminController::class, 'create'])->name('create');
                Route::post('/', [FormateurAdminController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [FormateurAdminController::class, 'edit'])->name('edit')->whereNumber('id');
                Route::put('/{id}', [FormateurAdminController::class, 'update'])->name('update')->whereNumber('id');
                Route::delete('/{id}', [FormateurAdminController::class, 'destroy'])->name('destroy')->whereNumber('id');
                Route::put('/{id}/disponible', [FormateurAdminController::class, 'toggleDisponible'])->name('disponible')->whereNumber('id');
            });

            Route::get('/categories', [AdminCategorieController::class, 'index'])->name('categories');
            Route::post('/categories', [AdminCategorieController::class, 'store'])->name('categories.store');
            Route::put('/categories/{id}', [AdminCategorieController::class, 'update'])->name('categories.update')->whereNumber('id');
            Route::delete('/categories/{id}', [AdminCategorieController::class, 'destroy'])->name('categories.destroy')->whereNumber('id');

            Route::get('/support', [AdminSupportController::class, 'index'])->name('support');
            Route::put('/support/{id}/statut', [AdminSupportController::class, 'updateStatut'])->name('support.statut')->whereNumber('id');
        });

});