@extends('layouts.app')
@section('title', "Mes produits — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item active">Mes produits</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-grid"></i></div>
        <div>
          <h2>Mes produits</h2>
          <p>{{ $produits->total() }} produit(s) publié(s)</p>
        </div>
      </div>
      <a href="{{ route('artisan.produits.create') }}" class="btn-or">
        <i class="bi bi-plus-circle me-2"></i>Publier un produit
      </a>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('artisan.produits') }}" class="d-flex flex-wrap gap-3 align-items-end mb-4">
      <div>
        <label class="form-label-tissu">Statut</label>
        <select name="actif" class="form-select-tissu" style="width:160px;">
          <option value="">Tous</option>
          <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actifs</option>
          <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactifs</option>
        </select>
      </div>
      <button type="submit" class="btn-outline-or">Filtrer</button>
      <a href="{{ route('artisan.produits') }}" class="text-muted small">Réinitialiser</a>
    </form>

    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);overflow:hidden;">
      <table class="table-tissu mb-0">
        <thead>
          <tr>
            <th>Produit</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($produits as $produit)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width:44px;height:44px;border-radius:8px;background:var(--sable);overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    @if(!empty($produit->images[0]))
                      <img src="{{ asset('storage/'.$produit->images[0]) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else 🧵 @endif
                  </div>
                  <span class="fw-semibold">{{ $produit->nom }}</span>
                </div>
              </td>
              <td>{{ $produit->categorie?->nom ?? '—' }}</td>
              <td class="fw-bold" style="color:var(--or-dark);">{{ number_format($produit->prix, 0) }} MAD</td>
              <td>{{ $produit->stock }}</td>
              <td>
                <span class="badge-statut {{ $produit->is_active ? 'badge-actif' : 'badge-inactif' }}">
                  {{ $produit->is_active ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="{{ route('artisan.produits.edit', $produit->id) }}" class="btn-outline-or btn-sm">Modifier</a>
                  <form method="POST" action="{{ route('artisan.produits.destroy', $produit->id) }}" onsubmit="return confirm('Désactiver ce produit ?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Désactiver</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Aucun produit publié.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $produits->withQueryString()->links() }}</div>
  </div>
</div>
@endsection
