@extends('layouts.admin')
@section('title', 'Produits')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Produits</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="admin-page-title">Produits</h1>
    <p class="text-muted small mb-0">{{ $produits->total() }} produit(s) au total</p>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['#22c55e', \App\Models\Produit::where('is_active',true)->count(), 'Actifs'],
        ['#ef4444', \App\Models\Produit::where('stock',0)->count(), 'Rupture de stock'],
        ['#64748b', \App\Models\Produit::where('is_active',false)->count(), 'Inactifs'],
        ['#f59e0b', \App\Models\Produit::where('stock','<=',5)->where('stock','>',0)->count(), 'Stock faible'],
    ] as [$color, $val, $lbl])
    <div class="col-6 col-xl-3">
        <div class="mini-stat" style="--accent: {{ $color }}">
            <div class="val">{{ $val }}</div>
            <div class="lbl">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.produits') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
    <div><label>Recherche</label><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom produit…" style="width:200px;"></div>
    <div>
        <label>Catégorie</label>
        <select name="categorie_id" class="form-select" style="width:160px;">
            <option value="">Toutes</option>
            @foreach(\App\Models\Categorie::whereNull('parent_id')->get() as $cat)
                <option value="{{ $cat->id }}" {{ request('categorie_id')==$cat->id ? 'selected':'' }}>{{ $cat->nom }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Statut</label>
        <select name="actif" class="form-select" style="width:130px;">
            <option value="">Tous</option>
            <option value="1" {{ request('actif')=='1' ? 'selected':'' }}>Actifs</option>
            <option value="0" {{ request('actif')=='0' ? 'selected':'' }}>Inactifs</option>
        </select>
    </div>
    <button type="submit" class="btn btn-admin-primary btn-admin-sm">Filtrer</button>
    <a href="{{ route('admin.produits') }}" class="text-muted small">Réinitialiser</a>
</form>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr><th>Produit</th><th>Artisan</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Note</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($produits as $produit)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded overflow-hidden bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            @if(!empty($produit->images[0]))
                                <img src="{{ asset('storage/'.$produit->images[0]) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                            @else 🧵 @endif
                        </div>
                        <span class="small fw-semibold">{{ str($produit->nom)->limit(35) }}</span>
                    </div>
                </td>
                <td class="small">{{ $produit->artisan?->user?->nom_complet }}</td>
                <td class="text-muted small">{{ $produit->categorie?->nom ?? '—' }}</td>
                <td class="fw-bold text-warning">{{ number_format($produit->prix, 0, ',', ' ') }} MAD</td>
                <td class="fw-semibold {{ $produit->stock == 0 ? 'text-danger' : ($produit->stock <= 5 ? 'text-warning' : 'text-success') }}">{{ $produit->stock }}</td>
                <td class="text-warning small"><i class="fa-solid fa-star"></i> {{ number_format($produit->note_moyenne ?? 0, 1) }}</td>
                <td><span class="badge-statut-sm {{ $produit->is_active ? 'badge-actif' : 'badge-inactif' }}">{{ $produit->is_active ? 'Actif' : 'Inactif' }}</span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('catalogue.show', $produit->id) }}" class="btn btn-sm btn-outline-secondary btn-admin-sm">Voir</a>
                        <form method="POST" action="{{ route('admin.produits.toggle', $produit->id) }}">@csrf
                            <button type="submit" class="btn btn-outline-{{ $produit->is_active ? 'danger' : 'success' }}-sm btn-admin-sm">
                                {{ $produit->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-5">Aucun produit trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $produits->withQueryString()->links() }}</div>
@endsection
