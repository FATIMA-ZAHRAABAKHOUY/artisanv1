@extends('layouts.app')
@section('title', 'Mon Panier — Tissu Artisanal')

@section('breadcrumb')
  <li class="breadcrumb-item active">Mon Panier</li>
@endsection

@push('styles')
<style>
.panier-wrap { padding: 48px 0 80px; }
.panier-item {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 20px;
    margin-bottom: 12px; display: flex; align-items: center; gap: 16px;
}
.panier-item-img {
    width: 88px; height: 88px; border-radius: 10px;
    background: var(--sable); overflow: hidden; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 36px;
}
.panier-item-img img { width:100%; height:100%; object-fit:cover; }
.recap-card {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 28px;
    position: sticky; top: 90px;
}
.recap-row {
    display: flex; justify-content: space-between;
    font-size: 14px; padding: 10px 0;
    border-bottom: 1px solid var(--sable-dark);
}
.recap-row:last-child { border-bottom: none; }
.recap-row.total {
    font-family: 'Amiri', serif; font-size: 20px;
    font-weight: 700; color: var(--or-dark);
}
</style>
@endpush

@section('content')
<div class="panier-wrap">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-bag"></i></div>
      <div>
        <h2>Mon Panier</h2>
        <p>{{ count(session('panier', [])) }} article(s) dans votre panier</p>
      </div>
    </div>

    @if(empty(session('panier', [])))
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:72px;margin-bottom:20px;">🛒</div>
        <h3 style="font-family:'Amiri',serif;">Votre panier est vide</h3>
        <p style="color:var(--gris-doux);margin-bottom:28px;">Découvrez nos produits artisanaux</p>
        <a href="{{ route('catalogue.index') }}" class="btn-or">
          <i class="bi bi-grid me-2"></i>Explorer le catalogue
        </a>
      </div>
    @else
      <div class="row g-4">
        {{-- Articles --}}
        <div class="col-lg-8">
          @foreach(session('panier', []) as $id => $item)
            <div class="panier-item">
              <div class="panier-item-img">
                @if(!empty($item['image']))
                  <img src="{{ asset('storage/'.$item['image']) }}" alt="{{ $item['nom'] }}">
                @else
                  🧵
                @endif
              </div>
              <div style="flex:1;">
                <div style="font-family:'Amiri',serif;font-size:17px;font-weight:700;margin-bottom:4px;">
                  {{ $item['nom'] }}
                </div>
                <div style="font-size:13px;color:var(--gris-doux);margin-bottom:8px;">
                  <i class="bi bi-person me-1"></i>{{ $item['artisan'] }}
                </div>
                <div style="color:var(--or-dark);font-weight:700;font-size:16px;">
                  {{ number_format($item['prix'] * $item['quantite'], 2) }} MAD
                  <span style="font-size:13px;font-weight:400;color:var(--gris-doux);">
                    ({{ number_format($item['prix'], 2) }} × {{ $item['quantite'] }})
                  </span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                {{-- Quantité --}}
                <form method="POST" action="{{ route('panier.update', $id) }}"
                      style="display:flex;align-items:center;gap:6px;">
                  @csrf @method('PUT')
                  <button type="submit" name="action" value="moins"
                          style="width:30px;height:30px;border-radius:50%;border:1.5px solid var(--sable-dark);
                                 background:white;cursor:pointer;font-size:16px;">−</button>
                  <span style="width:24px;text-align:center;font-weight:600;">{{ $item['quantite'] }}</span>
                  <button type="submit" name="action" value="plus"
                          style="width:30px;height:30px;border-radius:50%;border:1.5px solid var(--sable-dark);
                                 background:white;cursor:pointer;font-size:16px;">+</button>
                </form>
                {{-- Supprimer --}}
                <form method="POST" action="{{ route('panier.supprimer', $id) }}">
                  @csrf @method('DELETE')
                  <button type="submit"
                          style="width:34px;height:34px;border-radius:50%;border:none;
                                 background:var(--sable);color:var(--rouge-fes);
                                 cursor:pointer;font-size:16px;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          @endforeach

          <form method="POST" action="{{ route('panier.vider') }}">
            @csrf @method('DELETE')
            <button type="submit"
                    style="background:none;border:none;color:var(--gris-doux);
                           font-size:13px;cursor:pointer;padding:8px 0;">
              <i class="bi bi-trash me-1"></i>Vider le panier
            </button>
          </form>
        </div>

        {{-- Récapitulatif --}}
        <div class="col-lg-4">
          <div class="recap-card">
            <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:20px;">
              Récapitulatif
            </h3>
            @php
              $sousTotal = collect(session('panier',[])) ->sum(fn($i) => $i['prix'] * $i['quantite']);
              $tva       = round($sousTotal * 0.20, 2);
              $total     = $sousTotal + $tva;
            @endphp
            <div class="recap-row">
              <span>Sous-total HT</span>
              <span>{{ number_format($sousTotal, 2) }} MAD</span>
            </div>
            <div class="recap-row">
              <span>TVA (20%)</span>
              <span>{{ number_format($tva, 2) }} MAD</span>
            </div>
            <div class="recap-row">
              <span>Livraison</span>
              <span style="color:var(--vert-atlas);">Calculée au checkout</span>
            </div>
            <div class="recap-row total" style="margin-top:8px;padding-top:16px;border-top:2px solid var(--or);">
              <span>Total TTC</span>
              <span>{{ number_format($total, 2) }} MAD</span>
            </div>
            <a href="{{ route('checkout.index') }}" class="btn-or w-100"
               style="margin-top:20px;padding:14px;display:block;text-align:center;font-size:15px;">
              <i class="bi bi-credit-card me-2"></i>Passer la commande
            </a>
            <a href="{{ route('catalogue.index') }}"
               style="display:block;text-align:center;margin-top:12px;
                      font-size:13px;color:var(--gris-doux);">
              <i class="bi bi-arrow-left me-1"></i>Continuer mes achats
            </a>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection