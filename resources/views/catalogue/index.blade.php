@extends('layouts.app')
@section('title', "Catalogue — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item active">Catalogue</li>
@endsection

@push('styles')
<style>
.catalogue-wrap { padding: 48px 0 80px; }
.filter-panel {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 24px;
    position: sticky; top: 80px;
}
.filter-panel h5 {
    font-size: 13px; letter-spacing: 1px; text-transform: uppercase;
    color: var(--gris-doux); margin-bottom: 16px; font-weight: 600;
}
.filter-label {
    font-size: 13px; font-weight: 500; color: var(--texte); margin-bottom: 8px; display: block;
}
.cat-chip {
    display: inline-block; padding: 5px 14px; border-radius: 20px;
    border: 1.5px solid var(--sable-dark); font-size: 13px; color: var(--texte);
    cursor: pointer; transition: all 0.2s; margin: 3px; text-decoration: none;
}
.cat-chip:hover, .cat-chip.active {
    border-color: var(--ame-terre); background: rgba(155,74,58,0.08); color: var(--ame-terre-dark);
}
.sort-bar {
    display: flex; align-items: center; gap: 12px;
    justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap;
}
.produit-grid .produit-card {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: all 0.3s ease;
    height: 100%; display: flex; flex-direction: column;
}
.produit-grid .produit-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
.produit-img {
    aspect-ratio: 4/3; overflow: hidden; background: var(--sable);
    display: flex; align-items: center; justify-content: center;
    font-size: 56px; position: relative;
}
.produit-img img { width:100%; height:100%; object-fit:cover; transition: transform 0.4s; }
.produit-card:hover .produit-img img { transform: scale(1.05); }
</style>
@endpush

@section('content')
<div class="catalogue-wrap">
  <div class="container-xl">
    <div class="row g-4">

      {{-- ── FILTRES ────────────────────────────────── --}}
      <div class="col-lg-3 d-none d-lg-block">
        <div class="filter-panel">
          <form method="GET" action="{{ route('catalogue.index') }}" id="filterForm">

            <h5>Catégories</h5>
            <div class="mb-4">
              <a href="{{ route('catalogue.index', request()->except('categorie_id')) }}"
                 class="cat-chip {{ !request('categorie_id') ? 'active' : '' }}">Tout</a>
              @foreach(\App\Models\Categorie::whereNull('parent_id')->withCount('produits')->get() as $cat)
                <a href="{{ route('catalogue.index', array_merge(request()->all(), ['categorie_id' => $cat->id])) }}"
                   class="cat-chip {{ request('categorie_id') == $cat->id ? 'active' : '' }}">
                  {{ $cat->nom }} ({{ $cat->produits_count }})
                </a>
              @endforeach
            </div>

            <h5>Prix (MAD)</h5>
            <div class="row g-2 mb-4">
              <div class="col-6">
                <input type="number" name="prix_min" value="{{ request('prix_min') }}"
                       class="form-control-tissu" placeholder="Min">
              </div>
              <div class="col-6">
                <input type="number" name="prix_max" value="{{ request('prix_max') }}"
                       class="form-control-tissu" placeholder="Max">
              </div>
            </div>

            <h5>Stock</h5>
            <div class="mb-4">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                <input type="checkbox" name="en_stock" value="1"
                       {{ request('en_stock') ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--or);">
                En stock uniquement
              </label>
            </div>

            <button type="submit" class="btn-or w-100">Appliquer</button>
            <a href="{{ route('catalogue.index') }}"
               style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--gris-doux);">
              Réinitialiser
            </a>
          </form>
        </div>
      </div>

      {{-- ── GRILLE PRODUITS ────────────────────────── --}}
      <div class="col-lg-9">

        {{-- Barre de tri --}}
        <div class="sort-bar">
          <p style="margin:0;font-size:14px;color:var(--gris-doux);">
            <strong style="color:var(--texte);">{{ $produits->total() }}</strong> produit{{ $produits->total() > 1 ? 's' : '' }} trouvé{{ $produits->total() > 1 ? 's' : '' }}
            @if(request('q'))
              pour « <strong>{{ request('q') }}</strong> »
            @endif
          </p>
          <div class="d-flex gap-2 align-items-center">
            <span style="font-size:13px;color:var(--gris-doux);">Trier par :</span>
            <select name="sort" class="form-control-tissu"
                    style="width:auto;padding:7px 12px;font-size:13px;"
                    onchange="document.getElementById('filterForm').submit()">
              <option value="created_at" {{ request('sort')=='created_at' ? 'selected' : '' }}>Plus récent</option>
              <option value="prix"       {{ request('sort')=='prix'       ? 'selected' : '' }}>Prix</option>
              <option value="nom"        {{ request('sort')=='nom'        ? 'selected' : '' }}>Nom A-Z</option>
            </select>
          </div>
        </div>

        @if($produits->isEmpty())
          <div style="text-align:center;padding:80px 20px;color:var(--gris-doux);">
            <div style="font-size:64px;margin-bottom:16px;">🔍</div>
            <h3 style="font-family:var(--font-serif);">Aucun produit trouvé</h3>
            <p>Essayez d'autres critères de recherche</p>
            <a href="{{ route('catalogue.index') }}" class="btn-or">Voir tout le catalogue</a>
          </div>
        @else
          <div class="row g-3 produit-grid">
            @foreach($produits as $produit)
              <div class="col-6 col-md-4">
                <div class="produit-card">
                  <div class="produit-img">
                    @if(!empty($produit->images[0]))
                      <img src="{{ asset('storage/'.$produit->images[0]) }}"
                           alt="{{ $produit->nom }}" loading="lazy">
                    @else
                      🧵
                    @endif
                    @if($produit->stock == 0)
                      <span class="produit-badge" style="background:var(--rouge-fes);">Rupture</span>
                    @endif
                  </div>
                  <div class="produit-body" style="padding:16px;flex:1;display:flex;flex-direction:column;">
                    <div class="produit-categorie">{{ $produit->categorie?->nom }}</div>
                    <div class="produit-nom" style="font-family:var(--font-serif);font-size:16px;font-weight:700;margin-bottom:6px;flex:1;">
                      <a href="{{ route('catalogue.show', $produit->id) }}"
                         style="color:inherit;text-decoration:none;">{{ $produit->nom }}</a>
                    </div>
                    <div style="font-size:12px;color:var(--gris-doux);margin-bottom:8px;">
                      <i class="bi bi-person me-1"></i>{{ $produit->artisan?->user?->nom_complet }}
                    </div>
                    <div style="color:var(--or);font-size:12px;">
                      @for($s=1;$s<=5;$s++)
                        <i class="bi bi-star{{ $s <= round($produit->note_moyenne) ? '-fill' : '' }}"></i>
                      @endfor
                    </div>
                  </div>
                  <div class="produit-footer">
                    <div class="produit-prix">
                      {{ number_format($produit->prix, 2) }}<span> MAD</span>
                    </div>
                    @auth
                      <form method="POST" action="{{ route('panier.ajouter', $produit->id) }}">
                        @csrf
                        <button type="submit" class="btn-panier" title="Ajouter au panier"
                                {{ $produit->stock == 0 ? 'disabled' : '' }}>
                          <i class="bi bi-bag-plus"></i>
                        </button>
                      </form>
                    @else
                      <a href="{{ route('login') }}" class="btn-panier">
                        <i class="bi bi-bag-plus"></i>
                      </a>
                    @endauth
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Pagination --}}
          <div class="d-flex justify-content-center mt-4">
            {{ $produits->withQueryString()->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection