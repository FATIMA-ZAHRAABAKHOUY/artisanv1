@extends('layouts.app')
@section('title', "Espace Artisan — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item active">Espace Artisan</li>
@endsection

@push('styles')
<style>
.artisan-wrap { padding: 48px 0 80px; }
.dash-card {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 24px;
    box-shadow: var(--shadow-sm); height: 100%;
}
.dash-card h3 {
    font-family: var(--font-serif); font-size: 18px; margin-bottom: 16px;
    padding-bottom: 12px; border-bottom: 2px solid var(--sable-dark);
    display: flex; align-items: center; gap: 10px;
}
.kpi-box {
    text-align: center; padding: 20px 16px;
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); box-shadow: var(--shadow-sm);
    position: relative; overflow: hidden;
}
.kpi-box::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.kpi-box.or::before    { background: var(--ame-terre); }
.kpi-box.indigo::before{ background: var(--ame-charbon); }
.kpi-box.vert::before  { background: var(--vert-atlas); }
.kpi-box.rouge::before { background: var(--rouge-fes); }
.kpi-lbl { font-size: 13px; color: var(--gris-doux); margin-top: 6px; }
.alert-verify {
    background: #fef3c7; border: 1.5px solid #f59e0b;
    border-radius: var(--radius); padding: 16px 20px;
    margin-bottom: 24px; display: flex; align-items: center; gap: 14px;
}
</style>
@endpush

@section('content')
<div class="artisan-wrap">
  <div class="container-xl">

    {{-- Alerte si non vérifié --}}
    @if(!auth()->user()->artisan?->is_verified)
      <div class="alert-verify">
        <i class="bi bi-exclamation-triangle-fill" style="font-size:22px;color:#f59e0b;flex-shrink:0;"></i>
        <div>
          <strong>Compte en attente de validation</strong><br>
          <span style="font-size:14px;color:#92400e;">
            Votre profil artisan est en cours de vérification par l'administrateur de la coopérative.
            Vous pourrez publier des produits une fois validé.
          </span>
        </div>
      </div>
    @endif

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-palette"></i></div>
        <div>
          <h2>Bonjour, {{ auth()->user()->prenom }} 👋</h2>
          <p>{{ auth()->user()->artisan?->specialite }} — Membre depuis {{ auth()->user()->artisan?->date_adhesion?->format('d/m/Y') }}</p>
        </div>
      </div>
      @include('partials.date-theme-widget')
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
      @php
        $artisan     = auth()->user()->artisan;
        $nbProduits  = $artisan?->produits()->where('is_active',true)->count() ?? 0;
        $nbCommandes = \DB::table('lignes_commande')
                          ->join('produits','produits.id','=','lignes_commande.produit_id')
                          ->join('commandes','commandes.id','=','lignes_commande.commande_id')
                          ->where('produits.artisan_id', $artisan?->id)
                          ->whereNotIn('commandes.statut',['cancelled'])
                          ->distinct('commandes.id')->count('commandes.id');
        $revenus     = \DB::table('lignes_commande')
                          ->join('produits','produits.id','=','lignes_commande.produit_id')
                          ->join('commandes','commandes.id','=','lignes_commande.commande_id')
                          ->where('produits.artisan_id', $artisan?->id)
                          ->where('commandes.statut','delivered')
                          ->sum('lignes_commande.sous_total');
        $nbFormations= $artisan?->formations()->where('is_active',true)->count() ?? 0;
      @endphp
      <div class="col-6 col-md-3">
        <div class="kpi-box or">
          <div class="kpi-val">{{ $nbProduits }}</div>
          <div class="kpi-lbl">Produits actifs</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-box indigo">
          <div class="kpi-val">{{ $nbCommandes }}</div>
          <div class="kpi-lbl">Commandes reçues</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-box vert">
          <div class="kpi-val">{{ number_format($revenus, 0) }}</div>
          <div class="kpi-lbl">Revenus (MAD)</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="kpi-box rouge">
          <div class="kpi-val">{{ $nbFormations }}</div>
          <div class="kpi-lbl">Formations actives</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      {{-- Dernières commandes --}}
      <div class="col-lg-7">
        <div class="dash-card">
          <h3>
            <i class="bi bi-bag-check" style="color:var(--or);"></i>
            Dernières commandes
            <a href="{{ route('artisan.commandes') }}" style="font-size:13px;color:var(--or-dark);margin-left:auto;font-family:var(--font-sans);">Voir tout</a>
          </h3>
          @php
            $commandes = \DB::table('commandes')
              ->join('lignes_commande','lignes_commande.commande_id','=','commandes.id')
              ->join('produits','produits.id','=','lignes_commande.produit_id')
              ->join('users','users.id','=','commandes.client_id')
              ->where('produits.artisan_id', $artisan?->id)
              ->select('commandes.id','commandes.statut','commandes.total_ttc',
                       'commandes.created_at',\DB::raw("users.nom || ' ' || users.prenom as client"))
              ->distinct()->orderByDesc('commandes.created_at')->limit(5)->get();
          @endphp
          @forelse($commandes as $cmd)
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:12px 0;border-bottom:1px solid var(--sable-dark);flex-wrap:wrap;gap:8px;">
              <div>
                <div style="font-weight:600;font-size:14px;">Commande #{{ $cmd->id }}</div>
                <div style="font-size:12px;color:var(--gris-doux);">
                  {{ $cmd->client }} · {{ \Carbon\Carbon::parse($cmd->created_at)->format('d/m/Y') }}
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:10px;">
                @php $bMap=['pending'=>'badge-pending','confirmed'=>'badge-confirmed',
                            'processing'=>'badge-processing','shipped'=>'badge-shipped',
                            'delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'];
                @endphp
                <span class="badge-statut {{ $bMap[$cmd->statut] ?? '' }}" style="font-size:11px;">
                  {{ $cmd->statut }}
                </span>
                <span style="font-weight:700;color:var(--or-dark);font-size:14px;">
                  {{ number_format($cmd->total_ttc, 0) }} MAD
                </span>
              </div>
            </div>
          @empty
            <p style="color:var(--gris-doux);font-size:14px;">Aucune commande pour le moment.</p>
          @endforelse
        </div>
      </div>

      {{-- Mes produits --}}
      <div class="col-lg-5">
        <div class="dash-card">
          <h3>
            <i class="bi bi-grid" style="color:var(--or);"></i>
            Mes Produits
            <a href="{{ route('artisan.produits') }}" style="font-size:13px;color:var(--or-dark);margin-left:auto;font-family:var(--font-sans);">Gérer</a>
          </h3>
          @php $produits = $artisan?->produits()->with('categorie')->take(5)->get() ?? collect(); @endphp
          @forelse($produits as $p)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;
                        border-bottom:1px solid var(--sable-dark);">
              <div style="width:44px;height:44px;border-radius:8px;background:var(--sable);
                          overflow:hidden;flex-shrink:0;display:flex;align-items:center;
                          justify-content:center;font-size:20px;">
                @if(!empty($p->images[0]))
                  <img src="{{ asset('storage/'.$p->images[0]) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                @else 🧵 @endif
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  {{ $p->nom }}
                </div>
                <div style="font-size:12px;color:var(--gris-doux);">
                  Stock : {{ $p->stock }} · {{ number_format($p->prix,0) }} MAD
                </div>
              </div>
              <span style="font-size:11px;padding:3px 8px;border-radius:20px;
                           background:{{ $p->is_active ? '#d1fae5' : '#fee2e2' }};
                           color:{{ $p->is_active ? '#065f46' : '#991b1b' }};">
                {{ $p->is_active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
          @empty
            <p style="color:var(--gris-doux);font-size:14px;">Aucun produit publié.</p>
          @endforelse
          <a href="{{ route('artisan.produits.create') }}" class="btn-or w-100"
             style="margin-top:14px;padding:10px;display:block;text-align:center;font-size:14px;">
            <i class="bi bi-plus-circle me-2"></i>Publier un produit
          </a>
        </div>
      </div>
    </div>

    {{-- Mes Formations --}}
    @php
      $formations = $artisan?->formations()
          ->withCount(['inscriptions as inscriptions_count',
                       'inscriptions as en_cours' => fn($q) => $q->where('statut_inscription','en_cours')])
          ->orderByDesc('created_at')->take(4)->get() ?? collect();
    @endphp
    <div class="dash-card mt-4">
      <h3 style="margin-bottom:16px;">
        <i class="bi bi-mortarboard" style="color:var(--or);"></i>
        Mes Formations
        <a href="{{ route('artisan.formations') }}" style="font-size:13px;color:var(--or-dark);margin-left:auto;font-family:var(--font-sans);">Gérer</a>
      </h3>
      @forelse($formations as $f)
        <div style="display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--sable-dark);flex-wrap:wrap;">
          {{-- Image ou icône --}}
          <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;flex-shrink:0;
                      background:linear-gradient(135deg,var(--ame-charbon-deep),var(--ame-terre-dark));
                      display:flex;align-items:center;justify-content:center;">
            @if($f->image)
              <img src="{{ $f->image_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
              <i class="bi bi-mortarboard" style="color:rgba(255,255,255,0.35);font-size:20px;"></i>
            @endif
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $f->titre }}
            </div>
            <div style="font-size:12px;color:var(--gris-doux);">
              <i class="bi bi-calendar3 me-1"></i>{{ $f->date_debut?->format('d/m/Y') }}
              &nbsp;·&nbsp;{{ $f->inscriptions_count }} inscrit(s) · {{ $f->en_cours ?? 0 }} en cours
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <span style="font-size:11px;padding:3px 10px;border-radius:20px;
                         background:{{ $f->is_active ? '#d1fae5' : '#fee2e2' }};
                         color:{{ $f->is_active ? '#065f46' : '#991b1b' }};">
              {{ $f->is_active ? 'Active' : 'Inactive' }}
            </span>
            <a href="{{ route('artisan.formations.edit', $f->id) }}"
               style="font-size:12px;color:var(--or-dark);text-decoration:none;padding:4px 10px;
                      border:1px solid var(--sable-dark);border-radius:var(--radius-sm);">
              Modifier
            </a>
          </div>
        </div>
      @empty
        <p style="color:var(--gris-doux);font-size:14px;margin-bottom:14px;">
          Vous n'avez pas encore créé de formation.
        </p>
      @endforelse
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
        <a href="{{ route('artisan.formations.create') }}" class="btn-or"
           style="padding:10px 22px;font-size:14px;">
          <i class="bi bi-plus-circle me-2"></i>Créer une formation
        </a>
        @if($formations->isNotEmpty())
          <a href="{{ route('artisan.formations') }}" class="btn-outline-or"
             style="padding:10px 18px;font-size:14px;">
            <i class="bi bi-list-ul me-2"></i>Toutes mes formations
          </a>
        @endif
      </div>
    </div>

    {{-- Revenus graphique placeholder --}}
    <div class="dash-card mt-4">
      <h3><i class="bi bi-bar-chart" style="color:var(--or);"></i>Revenus des 6 derniers mois</h3>
      @php
        $revenusParMois = \DB::table('lignes_commande')
          ->join('produits','produits.id','=','lignes_commande.produit_id')
          ->join('commandes','commandes.id','=','lignes_commande.commande_id')
          ->where('produits.artisan_id', $artisan?->id)
          ->where('commandes.statut','delivered')
          ->where('commandes.created_at','>=',now()->subMonths(6))
          ->selectRaw("TO_CHAR(DATE_TRUNC('month',commandes.created_at),'MM/YYYY') as mois,
                       SUM(lignes_commande.sous_total) as total")
          ->groupBy(\DB::raw("DATE_TRUNC('month',commandes.created_at)"))
          ->orderBy(\DB::raw("DATE_TRUNC('month',commandes.created_at)"))
          ->get();
        $maxVal = $revenusParMois->max('total') ?: 1;
      @endphp
      <div style="display:flex;align-items:flex-end;gap:12px;height:120px;padding:0 8px;">
        @foreach($revenusParMois as $rm)
          @php $h = max(4, ($rm->total / $maxVal) * 100); @endphp
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
            <div style="font-size:11px;color:var(--gris-doux);font-weight:600;">
              {{ number_format($rm->total, 0) }}
            </div>
            <div style="width:100%;height:{{ $h }}%;background:linear-gradient(180deg,var(--ame-terre),var(--ame-terre-dark));
                        border-radius:4px 4px 0 0;min-height:4px;"></div>
            <div style="font-size:11px;color:var(--gris-doux);">{{ $rm->mois }}</div>
          </div>
        @endforeach
        @if($revenusParMois->isEmpty())
          <p style="color:var(--gris-doux);font-size:14px;width:100%;text-align:center;">
            Aucune vente encore enregistrée.
          </p>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection