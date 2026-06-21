@extends('layouts.app')
@section('title', 'Mes produits — Espace Fournisseur')

@push('styles')
<style>
.fournisseur-page { background: var(--sable); padding: 32px 0 64px; min-height: calc(100vh - 120px); }
.prod-inline-form { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.prod-inline-form input[type="number"] { width: 100px; }
</style>
@endpush

@section('content')
@include('fournisseur.partials.header')

<div class="fournisseur-page">
  <div class="container-xl">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-box-seam"></i></div>
        <div>
          <h2>Mes produits</h2>
          <p>{{ $fournisseur->nom }} — Mettez à jour vos prix et disponibilités</p>
        </div>
      </div>
      <a href="{{ route('fournisseur.dashboard') }}" class="btn-outline-or btn btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Tableau de bord
      </a>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-3">{{ session('success') }}</div>
    @endif

    {{-- MATÉRIAUX --}}
    <div class="card-tissu mb-4" style="padding:20px;">
      <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">🧵 Matériaux</h3>

      @if($materiaux->count())
        <div style="overflow-x:auto;">
          <table class="table table-tissu mb-0">
            <thead>
              <tr>
                <th>Produit</th>
                <th>Formation liée</th>
                <th>Prix / Stock</th>
                <th>Recommandé</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($materiaux as $m)
              <tr>
                <td>
                  <div style="font-weight:600;">{{ $m->nom_produit }}</div>
                  <div style="font-size:12px;color:var(--gris-doux);">{{ $m->materiau?->nom }}</div>
                </td>
                <td style="font-size:13px;color:var(--gris-doux);">{{ $m->materiau?->formation?->titre ?? '—' }}</td>
                <td colspan="2">
                  <form method="POST" action="{{ route('fournisseur.produits.materiau.update', $m->id) }}" class="prod-inline-form">
                    @csrf @method('PUT')
                    <input type="number" name="prix_unitaire" value="{{ $m->prix_unitaire }}" step="0.01" min="0"
                           class="form-control form-control-tissu" required>
                    <input type="text" name="unite_prix" value="{{ $m->unite_prix }}" placeholder="Unité"
                           class="form-control form-control-tissu" style="width:80px;">
                    <input type="url" name="url_produit" value="{{ $m->url_produit }}" placeholder="URL"
                           class="form-control form-control-tissu" style="min-width:140px;">
                    <input type="number" name="delai_livraison_min" value="{{ $m->delai_livraison_min }}" placeholder="Délai min"
                           class="form-control form-control-tissu" style="width:80px;" min="0">
                    <input type="number" name="delai_livraison_max" value="{{ $m->delai_livraison_max }}" placeholder="Délai max"
                           class="form-control form-control-tissu" style="width:80px;" min="0">
                    <label style="font-size:12px;display:flex;align-items:center;gap:4px;white-space:nowrap;">
                      <input type="checkbox" name="stock_disponible" value="1" {{ $m->stock_disponible ? 'checked' : '' }}>
                      En stock
                    </label>
                    @if($m->est_recommande)
                      <span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>
                    @else
                      <span style="font-size:12px;color:var(--gris-doux);">—</span>
                    @endif
                    <button type="submit" class="btn-or btn btn-sm" style="padding:5px 10px;font-size:12px;">Enregistrer</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">{{ $materiaux->withQueryString()->links() }}</div>
      @else
        <p style="color:var(--gris-doux);margin:0;">
          Aucun produit référencé. Contactez l'administrateur pour être ajouté au catalogue de matériaux/outils.
        </p>
      @endif
    </div>

    {{-- OUTILS --}}
    <div class="card-tissu" style="padding:20px;">
      <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">🔧 Outils</h3>

      @if($outils->count())
        <div style="overflow-x:auto;">
          <table class="table table-tissu mb-0">
            <thead>
              <tr>
                <th>Produit</th>
                <th>Formation liée</th>
                <th>Prix / Stock</th>
                <th>Recommandé</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($outils as $o)
              <tr>
                <td>
                  <div style="font-weight:600;">{{ $o->nom_produit }}</div>
                  <div style="font-size:12px;color:var(--gris-doux);">{{ $o->outil?->nom }}</div>
                </td>
                <td style="font-size:13px;color:var(--gris-doux);">{{ $o->outil?->formation?->titre ?? '—' }}</td>
                <td colspan="2">
                  <form method="POST" action="{{ route('fournisseur.produits.outil.update', $o->id) }}" class="prod-inline-form">
                    @csrf @method('PUT')
                    <input type="number" name="prix_unitaire" value="{{ $o->prix_unitaire }}" step="0.01" min="0"
                           class="form-control form-control-tissu" required>
                    <input type="text" name="unite_prix" value="{{ $o->unite_prix }}" placeholder="Unité"
                           class="form-control form-control-tissu" style="width:80px;">
                    <input type="url" name="url_produit" value="{{ $o->url_produit }}" placeholder="URL"
                           class="form-control form-control-tissu" style="min-width:140px;">
                    <input type="number" name="delai_livraison_min" value="{{ $o->delai_livraison_min }}" placeholder="Délai min"
                           class="form-control form-control-tissu" style="width:80px;" min="0">
                    <input type="number" name="delai_livraison_max" value="{{ $o->delai_livraison_max }}" placeholder="Délai max"
                           class="form-control form-control-tissu" style="width:80px;" min="0">
                    <label style="font-size:12px;display:flex;align-items:center;gap:4px;white-space:nowrap;">
                      <input type="checkbox" name="stock_disponible" value="1" {{ $o->stock_disponible ? 'checked' : '' }}>
                      En stock
                    </label>
                    @if($o->est_recommande)
                      <span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>
                    @else
                      <span style="font-size:12px;color:var(--gris-doux);">—</span>
                    @endif
                    <button type="submit" class="btn-or btn btn-sm" style="padding:5px 10px;font-size:12px;">Enregistrer</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">{{ $outils->withQueryString()->links() }}</div>
      @else
        <p style="color:var(--gris-doux);margin:0;">
          Aucun outil référencé. Contactez l'administrateur pour être ajouté au catalogue de matériaux/outils.
        </p>
      @endif
    </div>
  </div>
</div>
@endsection
