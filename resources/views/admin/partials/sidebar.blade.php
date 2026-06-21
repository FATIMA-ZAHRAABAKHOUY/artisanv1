{{-- ================================================================
     resources/views/admin/partials/sidebar.blade.php
================================================================ --}}
<div class="admin-sidebar d-none d-lg-block">
    <div style="padding:0 20px 20px;border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="font-family:'Amiri',serif;font-size:18px;color:white;">Administration</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.4);">Coopérative Tissu Artisanal</div>
    </div>
    @php
        $enAttente = \App\Models\Artisan::where('is_verified', false)->count();
        $ticketsOuverts = \App\Models\Support::whereIn('statut', ['ouvert', 'en_cours'])->count();
    @endphp
    <div class="section-lbl">Principal</div>
    <a href="{{ route('admin.dashboard') }}"   class="nav-link {{ request()->routeIs('admin.dashboard')   ? 'active':'' }}"><i class="bi bi-speedometer2"></i>Tableau de bord</a>

    <div class="section-lbl">Gestion</div>
    <a href="{{ route('admin.users') }}"       class="nav-link {{ request()->routeIs('admin.users*')       ? 'active':'' }}"><i class="bi bi-people"></i>Utilisateurs</a>
    <a href="{{ route('admin.artisans') }}"    class="nav-link {{ request()->routeIs('admin.artisans*')    ? 'active':'' }}">
        <i class="bi bi-palette"></i>Artisans
        @if($enAttente > 0)<span style="background:var(--rouge-fes);color:white;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:auto;">{{ $enAttente }}</span>@endif
    </a>
    <a href="{{ route('admin.produits') }}"    class="nav-link {{ request()->routeIs('admin.produits*')    ? 'active':'' }}"><i class="bi bi-grid-3x3-gap"></i>Produits</a>
    <a href="{{ route('admin.commandes') }}"   class="nav-link {{ request()->routeIs('admin.commandes*')   ? 'active':'' }}"><i class="bi bi-bag-check"></i>Commandes</a>
    <a href="{{ route('admin.livraisons') }}"  class="nav-link {{ request()->routeIs('admin.livraisons*')  ? 'active':'' }}"><i class="bi bi-truck"></i>Livraisons</a>

    <div class="section-lbl">Formations</div>
    <a href="{{ route('admin.formations') }}"  class="nav-link {{ request()->routeIs('admin.formations*')  ? 'active':'' }}"><i class="bi bi-mortarboard"></i>Formations</a>
    <a href="{{ route('admin.formateurs.index') }}" class="nav-link {{ request()->routeIs('admin.formateurs*') ? 'active':'' }}"><i class="bi bi-person-video3"></i>Formateurs</a>
    <a href="{{ route('admin.fournisseurs.index') }}" class="nav-link {{ request()->routeIs('admin.fournisseurs*') ? 'active':'' }}"><i class="bi bi-building"></i>Fournisseurs</a>

    <div class="section-lbl">Config</div>
    <a href="{{ route('admin.categories') }}"  class="nav-link {{ request()->routeIs('admin.categories*')  ? 'active':'' }}"><i class="bi bi-tags"></i>Catégories</a>
    <a href="{{ route('admin.support') }}"     class="nav-link {{ request()->routeIs('admin.support*')     ? 'active':'' }}"><i class="bi bi-headset"></i>Support
        @if($ticketsOuverts > 0)<span style="background:var(--rouge-fes);color:white;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:auto;">{{ $ticketsOuverts }}</span>@endif
    </a>

    <div style="padding:20px;margin-top:auto;">
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;
           color:rgba(255,255,255,0.5);font-size:13px;text-decoration:none;">
            <i class="bi bi-arrow-left"></i>Retour au site
        </a>
    </div>
</div>