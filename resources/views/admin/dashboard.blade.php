@extends('layouts.app')
@section('title', 'Tableau de bord — Administration')

@push('styles')
<style>
.admin-layout { display:flex; min-height:calc(100vh - 120px); }
.admin-sidebar {
    width:240px; flex-shrink:0; background:var(--indigo);
    min-height:100%; display:flex; flex-direction:column;
}
.admin-sidebar .section-lbl {
    font-size:10px; letter-spacing:1.5px; text-transform:uppercase;
    color:rgba(255,255,255,0.28); padding:16px 20px 5px; font-weight:600;
}
.admin-sidebar .nav-link {
    display:flex; align-items:center; gap:10px; padding:10px 20px;
    color:rgba(255,255,255,0.68); font-size:13.5px; text-decoration:none;
    transition:0.2s; border-left:3px solid transparent;
}
.admin-sidebar .nav-link:hover { color:white; background:rgba(255,255,255,0.06); }
.admin-sidebar .nav-link.active {
    color:var(--or-light); background:rgba(200,145,58,0.12); border-left-color:var(--or);
}
.admin-main { flex:1; background:var(--sable); padding:28px 32px; min-width:0; }
.stat-mini {
    background:white; border-radius:var(--radius); border:1px solid var(--sable-dark);
    padding:14px 16px; display:flex; align-items:center; gap:12px;
}
.stat-mini .val { font-family:'Amiri',serif; font-size:22px; font-weight:700; line-height:1; }
.stat-mini .lbl { font-size:12px; color:var(--gris-doux); }
.revenue-chart { display:flex; align-items:flex-end; gap:10px; height:100px; padding-top:10px; }
.revenue-bar-wrap { flex:1; text-align:center; }
.revenue-bar {
    width:100%; max-width:40px; margin:0 auto; border-radius:4px 4px 0 0;
    background:linear-gradient(180deg,var(--or-light),var(--or-dark));
    transition:height 0.6s ease;
}
.widget-card {
    background:white; border-radius:var(--radius); border:1px solid var(--sable-dark);
    padding:20px; margin-bottom:16px;
}
.widget-title { font-family:'Amiri',serif; font-size:17px; margin-bottom:14px; }
.alert-banner {
    border-radius:var(--radius); padding:12px 16px; margin-bottom:16px;
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;
}
@media (max-width:991px) {
    .admin-layout { flex-direction:column; }
    .admin-sidebar { width:100%; }
    .admin-main { padding:16px; }
}
</style>
@endpush

@section('content')
@php
    use App\Models\{User, Artisan, Produit, Commande, Paiement, Formation, InscriptionFormation, Livraison};
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Carbon;
    Carbon::setLocale('fr');

    $nbClients  = User::where('role','client')->count();
    $nbArtisans = Artisan::where('is_verified',true)->count();
    $nbLivrees  = Commande::where('statut','delivered')->count();
    $ca         = Paiement::where('statut','paid')->sum('montant');
    $nbProduits   = Produit::where('is_active',true)->count();
    $nbFormations = Formation::where('is_active',true)->count();
    $nbInscripts  = InscriptionFormation::where('statut_inscription','en_cours')->count();
    $nbEnAttente  = Commande::where('statut','pending')->count();
    $commandes = Commande::with('client')->latest()->take(7)->get();
    $attentes = Artisan::with('user')->where('is_verified',false)->take(4)->get();
    $revenus = DB::table('paiements')
        ->join('commandes','commandes.id','=','paiements.commande_id')
        ->where('paiements.statut','paid')
        ->where('commandes.created_at','>=',now()->subMonths(6))
        ->selectRaw("TO_CHAR(DATE_TRUNC('month',commandes.created_at),'Mon YYYY') as mois, SUM(paiements.montant) as total")
        ->groupBy(DB::raw("DATE_TRUNC('month',commandes.created_at)"))
        ->orderBy(DB::raw("DATE_TRUNC('month',commandes.created_at)"))
        ->get();
    $maxVal = $revenus->max('total') ?: 1;
    $ruptures = Produit::where('is_active',true)->where('stock',0)->with('artisan.user')->take(4)->get();
    $formations = Formation::with('artisan.user')
        ->withCount(['inscriptions as en_cours' => fn($q) => $q->where('statut_inscription','en_cours')])
        ->latest()->take(4)->get();
    $adminNotifs = DB::table('notifications')->latest('created_at')->take(5)->get();
    $sansLivreur = Livraison::sansLivreurActives()->count();
    $ruptureCount = Produit::where('is_active',true)->where('stock',0)->count();
    $bMap = ['pending'=>'pending','confirmed'=>'confirmed','processing'=>'processing',
             'shipped'=>'shipped','delivered'=>'delivered','cancelled'=>'cancelled'];
@endphp

<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        {{-- HEADER --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0 0 4px;">Tableau de bord</h1>
                <div style="font-size:13px;color:var(--gris-doux);">
                    Bienvenue, <strong>{{ auth()->user()->prenom }}</strong>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                @include('partials.date-theme-widget')
                <a href="{{ route('home') }}" class="btn-outline-or btn btn-sm">Voir le site</a>
            </div>
        </div>

        {{-- ALERTES --}}
        @if($sansLivreur > 0)
        <div class="alert-banner" style="background:#FEF3C7;border:1px solid #F59E0B;color:#92400E;">
            <span><i class="bi bi-exclamation-triangle me-2"></i>{{ $sansLivreur }} livraison(s) sans livreur assigné</span>
            <a href="{{ route('admin.livraisons') }}" class="btn btn-sm" style="background:#F59E0B;color:white;border:none;">Gérer</a>
        </div>
        @endif
        @if($ruptureCount > 0)
        <div class="alert-banner" style="background:#FEE2E2;border:1px solid var(--rouge-fes);color:var(--rouge-fes);">
            <span><i class="bi bi-box-seam me-2"></i>{{ $ruptureCount }} produit(s) en rupture de stock</span>
            <a href="{{ route('admin.produits') }}" class="btn btn-sm btn-danger">Voir produits</a>
        </div>
        @endif

        {{-- KPI ROW 1 --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="stat-card or">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-value kpi-counter" data-target="{{ $nbClients }}">0</div>
                    <div class="stat-label">Clients</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card indigo">
                    <div class="stat-icon"><i class="bi bi-palette"></i></div>
                    <div class="stat-value kpi-counter" data-target="{{ $nbArtisans }}">0</div>
                    <div class="stat-label">Artisans vérifiés</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card vert">
                    <div class="stat-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="stat-value kpi-counter" data-target="{{ $nbLivrees }}">0</div>
                    <div class="stat-label">Commandes livrées</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="stat-card rouge">
                    <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="stat-value kpi-counter" data-target="{{ (int)$ca }}" data-format="mad">0</div>
                    <div class="stat-label">C.A. total (MAD)</div>
                </div>
            </div>
        </div>

        {{-- KPI ROW 2 --}}
        <div class="row g-3 mb-4">
            @foreach([
                ['bi-grid', $nbProduits, 'Produits actifs'],
                ['bi-mortarboard', $nbFormations, 'Formations actives'],
                ['bi-person-check', $nbInscripts, 'Inscrits en cours'],
                ['bi-clock-history', $nbEnAttente, 'Commandes en attente'],
            ] as [$icon, $val, $lbl])
            <div class="col-6 col-xl-3">
                <div class="stat-mini">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--sable);display:flex;align-items:center;justify-content:center;color:var(--or);">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="val kpi-counter" data-target="{{ $val }}">0</div>
                        <div class="lbl">{{ $lbl }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- COMMANDES + WIDGETS --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="widget-card">
                    <div class="widget-title">Commandes récentes</div>
                    <div class="table-responsive">
                        <table class="table-tissu">
                            <thead>
                                <tr><th>#</th><th>Client</th><th>Ville</th><th>Total</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($commandes as $cmd)
                                <tr>
                                    <td><a href="{{ route('admin.commandes.show', $cmd->id) }}" style="font-weight:700;color:var(--or-dark);">#{{ $cmd->id }}</a></td>
                                    <td>{{ $cmd->client?->nom_complet ?? '—' }}</td>
                                    <td style="color:var(--gris-doux);">{{ $cmd->ville ?? '—' }}</td>
                                    <td style="font-weight:600;">{{ number_format($cmd->total_ttc, 0, ',', ' ') }} MAD</td>
                                    <td><span class="badge-statut badge-{{ $bMap[$cmd->statut] ?? 'pending' }}">{{ $cmd->statut }}</span></td>
                                    <td style="font-size:12px;color:var(--gris-doux);">{{ $cmd->created_at?->format('d/m/Y') }}</td>
                                    <td>
                                        @if($cmd->statut === 'pending')
                                        <form method="POST" action="{{ route('admin.commandes.statut', $cmd->id) }}" class="d-inline">@csrf @method('PUT')
                                            <input type="hidden" name="statut" value="confirmed">
                                            <button type="submit" class="btn-or btn btn-sm" style="padding:3px 8px;font-size:11px;">Confirmer</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Aucune commande</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                {{-- Artisans en attente --}}
                <div class="widget-card">
                    <div class="widget-title">Artisans en attente</div>
                    @forelse($attentes as $a)
                    <div class="d-flex align-items-center gap-2 mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--sable-dark)!important;">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--or),var(--or-dark));
                                    color:white;display:flex;align-items:center;justify-content:center;font-weight:700;">
                            {{ substr($a->user?->prenom ?? 'A', 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-weight:600;font-size:14px;">{{ $a->user?->nom_complet }}</div>
                            <div style="font-size:12px;color:var(--gris-doux);">{{ $a->specialite }} · {{ $a->user?->ville ?? '—' }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.artisans.valider', $a->id) }}">@csrf
                            <button type="submit" class="btn-or btn btn-sm" style="font-size:11px;">✓ Valider</button>
                        </form>
                    </div>
                    @empty
                    <p style="color:var(--vert-atlas);margin:0;">✅ Tous les artisans sont validés</p>
                    @endforelse
                </div>

                {{-- Revenus CSS --}}
                <div class="widget-card mb-0">
                    <div class="widget-title">Revenus (6 mois)</div>
                    <div class="revenue-chart">
                        @forelse($revenus as $rev)
                        @php $h = max(4, ($rev->total / $maxVal) * 80); @endphp
                        <div class="revenue-bar-wrap">
                            <div style="font-size:10px;font-weight:600;color:var(--or-dark);margin-bottom:4px;">{{ number_format($rev->total/1000,1) }}K</div>
                            <div class="revenue-bar" style="height:{{ $h }}px;"></div>
                            <div style="font-size:10px;color:var(--gris-doux);margin-top:4px;">{{ $rev->mois }}</div>
                        </div>
                        @empty
                        <p style="font-size:13px;color:var(--gris-doux);">Aucune donnée</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM ROW --}}
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="widget-card">
                    <div class="widget-title">Produits en rupture</div>
                    <table class="table-tissu">
                        <thead><tr><th>Produit</th><th>Artisan</th></tr></thead>
                        <tbody>
                            @forelse($ruptures as $p)
                            <tr>
                                <td><a href="{{ route('admin.produits') }}">{{ str($p->nom)->limit(25) }}</a></td>
                                <td style="font-size:12px;">{{ $p->artisan?->user?->nom_complet ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Aucune rupture</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="widget-card">
                    <div class="widget-title">Dernières formations</div>
                    @forelse($formations as $f)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--sable-dark)!important;">
                        <div style="font-weight:600;font-size:14px;">{{ str($f->titre)->limit(30) }}</div>
                        <div style="font-size:12px;color:var(--gris-doux);">{{ $f->artisan?->user?->nom_complet }}</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span style="font-size:12px;">{{ $f->en_cours ?? 0 }}/{{ $f->places_max }} places</span>
                            <span class="badge-statut badge-{{ $f->is_active ? 'delivered' : 'cancelled' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--gris-doux);">Aucune formation</p>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-4">
                <div class="widget-card">
                    <div class="widget-title">Notifications récentes</div>
                    @forelse($adminNotifs as $n)
                    <div class="d-flex gap-2 mb-3">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--indigo);margin-top:5px;flex-shrink:0;"></div>
                        <div>
                            <div style="font-size:13px;">{{ str($n->message ?? $n->titre ?? '—')->limit(50) }}</div>
                            <div style="font-size:11px;color:var(--gris-doux);">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--gris-doux);">Aucune notification</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.kpi-counter').forEach(el => {
    const target = parseInt(el.dataset.target || 0, 10);
    const format = el.dataset.format;
    const duration = 600, steps = 30;
    let current = 0;
    const inc = target / steps;
    const timer = setInterval(() => {
        current += inc;
        if (current >= target) { current = target; clearInterval(timer); }
        const val = Math.round(current);
        el.textContent = format === 'mad' ? val.toLocaleString('fr-FR') : val;
    }, duration / steps);
});
</script>
@endpush
