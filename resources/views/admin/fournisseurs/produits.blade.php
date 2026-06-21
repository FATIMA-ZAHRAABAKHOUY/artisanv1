@extends('layouts.app')
@section('title', 'Produits — ' . $fournisseur->nom)

@push('styles')
@include('admin.partials.layout-styles')
@endpush

@section('content')
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0 0 4px;">Catalogue produits</h1>
                <div style="font-size:13px;color:var(--gris-doux);">{{ $fournisseur->nom }} — {{ $fournisseur->getTypeLabel() }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.fournisseurs.edit', $fournisseur->id) }}" class="btn-outline-or btn btn-sm">Modifier fournisseur</a>
                <a href="{{ route('admin.fournisseurs.index') }}" class="btn btn-sm" style="background:var(--sable-dark);">Retour</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-tissu success mb-3">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-tissu danger mb-3">{{ $errors->first() }}</div>
        @endif

        {{-- MATÉRIAUX --}}
        <div class="card-tissu mb-4">
            <h2 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:16px;">Matériaux fournis</h2>

            @if($fournisseur->materiaux->count())
            <div style="overflow-x:auto;margin-bottom:20px;">
                <table class="table table-tissu mb-0">
                    <thead>
                        <tr>
                            <th>Formation / Matériau</th>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Recommandé</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fournisseur->materiaux as $fm)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $fm->materiau?->nom ?? '—' }}</div>
                                <div style="font-size:12px;color:var(--gris-doux);">{{ $fm->materiau?->formation?->titre }}</div>
                            </td>
                            <td>
                                {{ $fm->nom_produit }}
                                @if($fm->reference_produit)<div style="font-size:11px;color:var(--gris-doux);">Réf. {{ $fm->reference_produit }}</div>@endif
                            </td>
                            <td style="white-space:nowrap;">{{ number_format($fm->prix_unitaire, 2) }} MAD@if($fm->unite_prix)/{{ $fm->unite_prix }}@endif</td>
                            <td>@if($fm->est_recommande)<span class="badge-statut badge-confirmed">⭐ Recommandé</span>@else — @endif</td>
                            <td>
                                <span class="badge-statut badge-{{ $fm->stock_disponible ? 'confirmed' : 'cancelled' }}">
                                    {{ $fm->stock_disponible ? 'En stock' : 'Rupture' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p style="color:var(--gris-doux);margin-bottom:20px;">Aucun matériau associé pour l'instant.</p>
            @endif

            <details style="border-top:1px solid var(--sable-dark);padding-top:16px;">
                <summary style="cursor:pointer;font-weight:600;color:var(--or-dark);">+ Ajouter un matériau</summary>
                <form method="POST" action="{{ route('admin.fournisseurs.materiaux.store', $fournisseur->id) }}" class="mt-3">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-tissu">Matériau de formation *</label>
                            <select name="materiau_id" class="form-select form-control-tissu" required>
                                <option value="">— Choisir —</option>
                                @foreach($materiauxOptions as $formationTitre => $items)
                                    <optgroup label="{{ $formationTitre }}">
                                        @foreach($items as $m)
                                            <option value="{{ $m->id }}">{{ $m->nom }} ({{ $m->quantite }} {{ $m->unite }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">Nom du produit *</label>
                            <input type="text" name="nom_produit" class="form-control form-control-tissu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Prix unitaire (MAD) *</label>
                            <input type="number" name="prix_unitaire" step="0.01" min="0" class="form-control form-control-tissu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Unité de prix</label>
                            <input type="text" name="unite_prix" class="form-control form-control-tissu" placeholder="m, rouleau…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Référence</label>
                            <input type="text" name="reference_produit" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">URL produit</label>
                            <input type="url" name="url_produit" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-tissu">Délai min (j)</label>
                            <input type="number" name="delai_livraison_min" min="0" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-tissu">Délai max (j)</label>
                            <input type="number" name="delai_livraison_max" min="0" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-6 d-flex gap-4 align-items-center">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="est_recommande" value="1"> Recommandé
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="stock_disponible" value="1" checked> En stock
                            </label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-or btn btn-sm">Ajouter au catalogue</button>
                        </div>
                    </div>
                </form>
            </details>
        </div>

        {{-- OUTILS --}}
        <div class="card-tissu">
            <h2 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:16px;">Outils fournis</h2>

            @if($fournisseur->outils->count())
            <div style="overflow-x:auto;margin-bottom:20px;">
                <table class="table table-tissu mb-0">
                    <thead>
                        <tr>
                            <th>Formation / Outil</th>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Recommandé</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fournisseur->outils as $fo)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $fo->outil?->nom ?? '—' }}</div>
                                <div style="font-size:12px;color:var(--gris-doux);">{{ $fo->outil?->formation?->titre }}</div>
                            </td>
                            <td>
                                {{ $fo->nom_produit }}
                                @if($fo->reference_produit)<div style="font-size:11px;color:var(--gris-doux);">Réf. {{ $fo->reference_produit }}</div>@endif
                            </td>
                            <td style="white-space:nowrap;">{{ number_format($fo->prix_unitaire, 2) }} MAD@if($fo->unite_prix)/{{ $fo->unite_prix }}@endif</td>
                            <td>@if($fo->est_recommande)<span class="badge-statut badge-confirmed">⭐ Recommandé</span>@else — @endif</td>
                            <td>
                                <span class="badge-statut badge-{{ $fo->stock_disponible ? 'confirmed' : 'cancelled' }}">
                                    {{ $fo->stock_disponible ? 'En stock' : 'Rupture' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p style="color:var(--gris-doux);margin-bottom:20px;">Aucun outil associé pour l'instant.</p>
            @endif

            <details style="border-top:1px solid var(--sable-dark);padding-top:16px;">
                <summary style="cursor:pointer;font-weight:600;color:var(--or-dark);">+ Ajouter un outil</summary>
                <form method="POST" action="{{ route('admin.fournisseurs.outils.store', $fournisseur->id) }}" class="mt-3">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-tissu">Outil de formation *</label>
                            <select name="outil_id" class="form-select form-control-tissu" required>
                                <option value="">— Choisir —</option>
                                @foreach($outilsOptions as $formationTitre => $items)
                                    <optgroup label="{{ $formationTitre }}">
                                        @foreach($items as $o)
                                            <option value="{{ $o->id }}">{{ $o->nom }} (×{{ $o->quantite }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">Nom du produit *</label>
                            <input type="text" name="nom_produit" class="form-control form-control-tissu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Prix (MAD) *</label>
                            <input type="number" name="prix_unitaire" step="0.01" min="0" class="form-control form-control-tissu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Unité de prix</label>
                            <input type="text" name="unite_prix" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-tissu">Référence</label>
                            <input type="text" name="reference_produit" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label-tissu">URL produit</label>
                            <input type="url" name="url_produit" class="form-control form-control-tissu">
                        </div>
                        <div class="col-md-4 d-flex gap-4 align-items-end">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="est_recommande" value="1"> Recommandé
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="stock_disponible" value="1" checked> En stock
                            </label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-or btn btn-sm">Ajouter au catalogue</button>
                        </div>
                    </div>
                </form>
            </details>
        </div>
    </div>
</div>
@endsection
