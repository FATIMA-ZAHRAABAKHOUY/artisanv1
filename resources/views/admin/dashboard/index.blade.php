@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Tableau de bord</li>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 mb-1 fw-bold">Tableau de bord</h2>
        <p class="text-muted small mb-0">Vue d'ensemble de la plateforme coopérative — données en temps réel</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dashboard.charts') }}" class="btn btn-sm btn-outline-secondary" id="refreshCharts" title="Rafraîchir les graphiques">
            <i class="fa-solid fa-rotate"></i> Actualiser
        </a>
        <a href="{{ url('/admin') }}" class="btn btn-sm btn-gold"><i class="fa-solid fa-table-columns me-1"></i> CRUD Filament</a>
    </div>
</div>

{{-- ═══ KPI AVANCÉS ═══ --}}
<div class="row g-3 mb-4">
    @foreach([
        ['val' => $kpis['taux_conversion'].'%', 'lbl' => 'Taux conversion', 'icon' => 'fa-chart-line'],
        ['val' => number_format($kpis['panier_moyen'], 2, ',', ' ').' MAD', 'lbl' => 'Panier moyen', 'icon' => 'fa-cart-shopping'],
        ['val' => $kpis['taux_satisfaction'].'%', 'lbl' => 'Satisfaction (avis)', 'icon' => 'fa-star'],
        ['val' => $kpis['taux_paiement_ok'].'%', 'lbl' => 'Paiements réussis', 'icon' => 'fa-credit-card'],
        ['val' => $kpis['taux_livraison_ok'].'%', 'lbl' => 'Livraisons OK', 'icon' => 'fa-truck-fast'],
        ['val' => $kpis['stock_total'], 'lbl' => 'Stock total', 'icon' => 'fa-boxes-stacked'],
    ] as $k)
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-advanced">
            <div class="val"><i class="fa-solid {{ $k['icon'] }} me-1 opacity-75"></i>{{ $k['val'] }}</div>
            <div class="lbl">{{ $k['lbl'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ═══ CARTES STATISTIQUES PRINCIPALES ═══ --}}
@php
$cards = [
    ['users_total', 'Utilisateurs', 'fa-users', '#3b82f6'],
    ['artisans_actifs', 'Artisans actifs', 'fa-palette', '#c8913a'],
    ['formateurs', 'Formateurs', 'fa-chalkboard-user', '#8b5cf6'],
    ['users_clients', 'Clients', 'fa-user', '#06b6d4'],
    ['users_apprenants', 'Apprenants', 'fa-user-graduate', '#10b981'],
    ['users_admins', 'Administrateurs', 'fa-user-shield', '#ef4444'],
    ['produits_total', 'Produits', 'fa-shirt', '#f59e0b'],
    ['produits_actifs', 'Produits actifs', 'fa-circle-check', '#22c55e'],
    ['produits_rupture', 'Rupture stock', 'fa-triangle-exclamation', '#ef4444'],
    ['categories', 'Catégories', 'fa-tags', '#6366f1'],
    ['avis_total', 'Avis clients', 'fa-star', '#eab308'],
    ['avis_moyenne', 'Note moyenne /5', 'fa-star-half-stroke', '#fbbf24'],
    ['commandes_total', 'Commandes', 'fa-bag-shopping', '#c06b5a'],
    ['commandes_pending', 'En attente', 'fa-clock', '#f97316'],
    ['commandes_confirmed', 'Confirmées', 'fa-check', '#3b82f6'],
    ['commandes_delivered', 'Livrées', 'fa-box-open', '#22c55e'],
    ['commandes_cancelled', 'Annulées', 'fa-ban', '#64748b'],
    ['ca_total', 'CA total (MAD)', 'fa-coins', '#c8913a', true],
    ['paiements_paid', 'Paiements OK', 'fa-credit-card', '#22c55e'],
    ['paiements_pending', 'Paiements attente', 'fa-hourglass', '#f59e0b'],
    ['livraisons_transit', 'Livraisons transit', 'fa-truck', '#3b82f6'],
    ['livraisons_done', 'Livraisons terminées', 'fa-truck-ramp-box', '#10b981'],
    ['formations_actives', 'Formations actives', 'fa-graduation-cap', '#8b5cf6'],
    ['formations_terminees', 'Formations terminées', 'fa-flag-checkered', '#64748b'],
    ['inscriptions_total', 'Inscriptions', 'fa-book-open', '#06b6d4'],
    ['progression_moyenne', 'Progression moy. %', 'fa-chart-simple', '#a855f7'],
    ['fournisseurs', 'Fournisseurs', 'fa-building', '#78716c'],
    ['suggestions_achat', 'Suggestions achat', 'fa-lightbulb', '#eab308'],
    ['notifications_non_lues', 'Notif. non lues', 'fa-bell', '#f43f5e'],
    ['support_ouverts', 'Tickets ouverts', 'fa-headset', '#ef4444'],
];
@endphp

<div class="row g-3 mb-4">
    @foreach($cards as $c)
    @php
        $val = $stats[$c[0]] ?? 0;
        if (!empty($c[4])) $val = number_format((float)$val, 2, ',', ' ');
    @endphp
    <div class="col-6 col-md-4 col-xl-3 col-xxl-2">
        <div class="kpi-card" style="--accent: {{ $c[3] }}">
            <div class="kpi-icon" style="background: {{ $c[3] }}22; color: {{ $c[3] }}"><i class="fa-solid {{ $c[2] }}"></i></div>
            <div class="kpi-value">{{ $val }}</div>
            <div class="kpi-label">{{ $c[1] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ═══ TOP PERFORMERS ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="dash-card"><div class="card-title"><i class="fa-solid fa-trophy"></i> Produit #1</div><p class="mb-0 fw-semibold">{{ $kpis['produit_top'] }}</p></div></div>
    <div class="col-md-3"><div class="dash-card"><div class="card-title"><i class="fa-solid fa-medal"></i> Artisan #1</div><p class="mb-0 fw-semibold">{{ $kpis['artisan_top'] }}</p></div></div>
    <div class="col-md-3"><div class="dash-card"><div class="card-title"><i class="fa-solid fa-graduation-cap"></i> Formation #1</div><p class="mb-0 fw-semibold">{{ str($kpis['formation_top'])->limit(40) }}</p></div></div>
    <div class="col-md-3"><div class="dash-card"><div class="card-title"><i class="fa-solid fa-truck-field"></i> Fournisseur #1</div><p class="mb-0 fw-semibold">{{ $kpis['fournisseur_top'] }}</p></div></div>
</div>

{{-- ═══ GRAPHIQUES LIGNE / BARRES ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-chart-line"></i> Évolution des commandes (12 mois)</div><canvas id="chartCommandesMois" height="220"></canvas></div>
    </div>
    <div class="col-lg-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-coins"></i> Revenus mensuels (MAD)</div><canvas id="chartRevenusMois" height="220"></canvas></div>
    </div>
    <div class="col-lg-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-chart-bar"></i> Top 10 artisans (CA)</div><canvas id="chartTopArtisans" height="220"></canvas></div>
    </div>
    <div class="col-lg-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-ranking-star"></i> Top 10 produits vendus</div><canvas id="chartTopProduits" height="220"></canvas></div>
    </div>
</div>

{{-- ═══ GRAPHIQUES DONUT / BARRES ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-chart-pie"></i> Produits par catégorie</div><canvas id="chartProduitsCat" height="200"></canvas></div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-users"></i> Utilisateurs par rôle</div><canvas id="chartUsersRole" height="200"></canvas></div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-bag-shopping"></i> État des commandes</div><canvas id="chartCommandesStatut" height="200"></canvas></div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-truck"></i> État des livraisons</div><canvas id="chartLivraisonsStatut" height="200"></canvas></div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-credit-card"></i> État des paiements</div><canvas id="chartPaiementsStatut" height="200"></canvas></div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-chart-simple"></i> Progression formations</div><canvas id="chartProgression" height="200"></canvas></div>
    </div>
    <div class="col-md-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-building"></i> Top fournisseurs recommandés</div><canvas id="chartFournisseurs" height="180"></canvas></div>
    </div>
    <div class="col-md-6">
        <div class="dash-card"><div class="card-title"><i class="fa-solid fa-user-plus"></i> Inscriptions par formation</div><canvas id="chartInscriptions" height="180"></canvas></div>
    </div>
</div>

{{-- ═══ WIDGETS + TIMELINE ═══ --}}
<div class="row g-3 mb-4">
    {{-- Timeline --}}
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Activité récente</div>
            <ul class="timeline">
                @forelse($timeline as $item)
                <li>
                    <div class="tl-icon" style="background: {{ $item['color'] }}22; color: {{ $item['color'] }}">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </div>
                    <div>
                        <div>{{ str($item['text'])->limit(55) }}</div>
                        <div class="tl-time">{{ $item['at']?->diffForHumans() }}</div>
                    </div>
                </li>
                @empty
                <li class="text-muted">Aucune activité récente.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Dernières commandes --}}
    <div class="col-lg-8">
        <div class="dash-card">
            <div class="card-title"><i class="fa-solid fa-bag-shopping"></i> Dernières commandes</div>
            <div class="table-responsive">
                <table class="table table-dash table-hover mb-0">
                    <thead><tr><th>#</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead>
                    <tbody>
                    @foreach($widgets['commandes'] as $cmd)
                    <tr>
                        <td>#{{ $cmd->id }}</td>
                        <td>{{ $cmd->client?->nom_complet ?? '—' }}</td>
                        <td>{{ number_format($cmd->total_ttc ?? 0, 2, ',', ' ') }} MAD</td>
                        <td><span class="badge-statut bg-secondary">{{ $cmd->statut }}</span></td>
                        <td class="text-muted">{{ $cmd->created_at?->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['users', 'Derniers utilisateurs', ['Nom', 'Email', 'Rôle'], fn($u) => [$u->nom_complet, $u->email, $u->role]],
        ['produits', 'Derniers produits', ['Produit', 'Artisan', 'Prix'], fn($p) => [str($p->nom)->limit(25), $p->artisan?->user?->nom_complet ?? '—', number_format($p->prix,2,',',' ').' MAD']],
        ['formations', 'Dernières formations', ['Titre', 'Artisan', 'Début'], fn($f) => [str($f->titre)->limit(25), $f->artisan?->user?->nom_complet ?? '—', $f->date_debut?->format('d/m/Y') ?? '—']],
        ['livraisons', 'Dernières livraisons', ['#', 'Client', 'Statut'], fn($l) => ['#'.$l->id, $l->commande?->client?->nom_complet ?? '—', $l->statut]],
        ['paiements', 'Derniers paiements', ['Réf.', 'Montant', 'Statut'], fn($p) => [$p->reference ?? '#'.$p->id, number_format($p->montant,2,',',' ').' MAD', $p->statut]],
        ['support', 'Tickets support', ['Objet', 'Utilisateur', 'Statut'], fn($s) => [str($s->objet)->limit(30), $s->user?->nom_complet ?? '—', $s->statut]],
        ['notifications', 'Notifications', ['Titre', 'Utilisateur', 'Lu'], fn($n) => [str($n->titre ?? $n->message)->limit(30), $n->user?->nom_complet ?? '—', $n->is_read ? 'Oui' : 'Non']],
    ] as $widget)
    <div class="col-lg-6">
        <div class="dash-card">
            <div class="card-title"><i class="fa-solid fa-list"></i> {{ $widget[1] }}</div>
            <div class="table-responsive">
                <table class="table table-dash table-sm mb-0">
                    <thead><tr>@foreach($widget[2] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
                    <tbody>
                    @foreach($widgets[$widget[0]] as $row)
                    <tr>@foreach($widget[3]($row) as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const chartsData = @json($charts);
const palette = ['#c8913a','#3b82f6','#22c55e','#ef4444','#8b5cf6','#06b6d4','#f59e0b','#ec4899','#64748b','#10b981'];
const chartInstances = {};

function makeLine(id, labels, data, label, color = '#c8913a') {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    chartInstances[id] = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: [{ label, data, borderColor: color, backgroundColor: color + '33', fill: true, tension: .35 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: window.dashChartDefaults.scales }
    });
}

function makeBar(id, labels, data, horizontal = false) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    chartInstances[id] = new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, backgroundColor: palette.slice(0, labels.length) }] },
        options: {
            indexAxis: horizontal ? 'y' : 'x',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: window.dashChartDefaults.scales
        }
    });
}

function makeDoughnut(id, labels, data) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    const legendColor = window.dashChartDefaults?.plugins?.legend?.labels?.color || '#94a3b8';
    chartInstances[id] = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: palette }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, color: legendColor } } } }
    });
}

document.addEventListener('DOMContentLoaded', initDashboardCharts);

function initDashboardCharts() {
    makeLine('chartCommandesMois', chartsData.commandesMois.labels, chartsData.commandesMois.data, 'Commandes');
    makeLine('chartRevenusMois', chartsData.revenusMois.labels, chartsData.revenusMois.data, 'Revenus', '#22c55e');
    makeBar('chartTopArtisans', chartsData.topArtisans.labels, chartsData.topArtisans.data, true);
    makeBar('chartTopProduits', chartsData.topProduits.labels, chartsData.topProduits.data, true);
    makeDoughnut('chartProduitsCat', chartsData.produitsCategorie.labels, chartsData.produitsCategorie.data);
    makeDoughnut('chartUsersRole', chartsData.usersRole.labels, chartsData.usersRole.data);
    makeDoughnut('chartCommandesStatut', chartsData.commandesStatut.labels, chartsData.commandesStatut.data);
    makeDoughnut('chartLivraisonsStatut', chartsData.livraisonsStatut.labels, chartsData.livraisonsStatut.data);
    makeDoughnut('chartPaiementsStatut', chartsData.paiementsStatut.labels, chartsData.paiementsStatut.data);
    makeBar('chartProgression', chartsData.progressionFormations.labels, chartsData.progressionFormations.data);
    if (chartsData.topFournisseurs.labels.length) {
        makeBar('chartFournisseurs', chartsData.topFournisseurs.labels, chartsData.topFournisseurs.data, true);
    }
    makeBar('chartInscriptions', chartsData.inscriptionsFormation.labels, chartsData.inscriptionsFormation.data);
}

document.getElementById('refreshCharts')?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
        const res = await fetch('{{ route('admin.dashboard.charts') }}', { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (json.success) {
            Object.values(chartInstances).forEach(c => c.destroy());
            Object.keys(chartInstances).forEach(k => delete chartInstances[k]);
            Object.assign(chartsData, json.charts);
            initDashboardCharts();
        }
    } catch (err) { console.error(err); }
});

/* Reconstruire les graphiques au changement jour/nuit */
window.addEventListener('admin-theme-changed', () => {
    if (Object.keys(chartInstances).length === 0) return;
    Object.values(chartInstances).forEach(c => c.destroy());
    Object.keys(chartInstances).forEach(k => delete chartInstances[k]);
    initDashboardCharts();
});
</script>
@endpush
