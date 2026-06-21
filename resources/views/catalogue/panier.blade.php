@extends('layouts.app')
@section('title', "Mon panier — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item active">Mon Panier</li>
@endsection

@push('styles')
<style>
.panier-wrap { padding: 48px 0 80px; }

.panier-item {
    background: white;
    border-radius: var(--radius);
    border: 1px solid var(--sable-dark);
    padding: 20px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s ease;
}
.panier-item:hover {
    box-shadow: var(--shadow-md);
    border-color: var(--or);
}

.panier-item-img {
    width: 88px;
    height: 88px;
    border-radius: 10px;
    background: var(--sable);
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    border: 1px solid var(--sable-dark);
}
.panier-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1.5px solid var(--sable-dark);
    background: white;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    line-height: 1;
}
.qty-btn:hover {
    border-color: var(--or);
    color: var(--or-dark);
    background: var(--sable);
}

.recap-card {
    background: white;
    border-radius: var(--radius);
    border: 1px solid var(--sable-dark);
    padding: 28px;
    position: sticky;
    top: 90px;
    box-shadow: var(--shadow-sm);
}

.recap-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    padding: 11px 0;
    border-bottom: 1px solid var(--sable-dark);
}
.recap-row.total {
    font-family: var(--font-serif);
    font-size: 22px;
    font-weight: 700;
    color: var(--or-dark);
    border-bottom: none;
    padding-top: 16px;
    margin-top: 6px;
    border-top: 2px solid var(--or);
}

.btn-suppr {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: var(--sable);
    color: var(--rouge-fes);
    cursor: pointer;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.btn-suppr:hover {
    background: #fee2e2;
    color: var(--rouge-fes);
    transform: scale(1.1);
}
</style>
@endpush

@section('content')
<div class="panier-wrap">
  <div class="container-xl">

    {{-- ── TITRE ─────────────────────────────────────────── --}}
    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-bag"></i></div>
      <div>
        <h2>Mon Panier</h2>
        <p>
          @php $nbArticles = collect(session('panier', []))->sum('quantite'); @endphp
          {{ $nbArticles }} article{{ $nbArticles > 1 ? 's' : '' }} dans votre panier
        </p>
      </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="alert-tissu success mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="alert-tissu error mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
      </div>
    @endif

    {{-- ── PANIER VIDE ───────────────────────────────────── --}}
    @if(empty(session('panier', [])))
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:80px;margin-bottom:20px;">🛒</div>
        <h3 style="font-family:var(--font-serif);font-size:26px;margin-bottom:10px;">
          Votre panier est vide
        </h3>
        <p style="color:var(--gris-doux);margin-bottom:28px;font-size:15px;">
          Découvrez nos produits artisanaux marocains
        </p>
        <a href="{{ route('catalogue.index') }}" class="btn-or"
           style="padding:12px 28px;font-size:15px;display:inline-flex;align-items:center;gap:8px;">
          <i class="bi bi-grid"></i>Explorer le catalogue
        </a>
      </div>

    {{-- ── PANIER REMPLI ─────────────────────────────────── --}}
    @else
      <div class="row g-4">

        {{-- ── ARTICLES ─────────────────────────────────── --}}
        <div class="col-lg-8">

          @foreach(session('panier', []) as $id => $item)
            <div class="panier-item">

              {{-- Image --}}
              <div class="panier-item-img">
                @if(!empty($item['image']))
                  <img src="{{ asset('storage/'.$item['image']) }}"
                       alt="{{ $item['nom'] }}" loading="lazy">
                @else
                  🧵
                @endif
              </div>

              {{-- Infos produit --}}
              <div style="flex:1;min-width:0;">
                <a href="{{ route('catalogue.show', $id) }}"
                   style="font-family:var(--font-serif);font-size:17px;font-weight:700;
                          color:var(--texte);text-decoration:none;display:block;
                          margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  {{ $item['nom'] }}
                </a>
                <div style="font-size:13px;color:var(--gris-doux);margin-bottom:8px;">
                  <i class="bi bi-person me-1"></i>{{ $item['artisan'] ?? '—' }}
                </div>
                <div style="color:var(--or-dark);font-weight:700;font-size:16px;">
                  {{ number_format($item['prix'] * $item['quantite'], 2) }} MAD
                  <span style="font-size:12.5px;font-weight:400;color:var(--gris-doux);">
                    ({{ number_format($item['prix'], 2) }} × {{ $item['quantite'] }})
                  </span>
                </div>
                @if(!empty($item['stock']) && $item['stock'] <= 5)
                  <div style="font-size:11.5px;color:#f59e0b;margin-top:4px;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Plus que {{ $item['stock'] }} en stock
                  </div>
                @endif
              </div>

              {{-- Actions : quantité + supprimer --}}
              <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">

                {{-- ── UPDATE quantité ── --}}
                <form method="POST" action="{{ route('panier.update', $id) }}"
                      style="display:flex;align-items:center;gap:6px;">
                  @csrf
                  @method('PUT')

                  {{-- Moins --}}
                  <button type="submit" name="action" value="moins"
                          class="qty-btn" title="Diminuer la quantité">
                    −
                  </button>

                  {{-- Quantité actuelle --}}
                  <span style="width:28px;text-align:center;
                               font-weight:700;font-size:15px;
                               color:var(--texte);">
                    {{ $item['quantite'] }}
                  </span>

                  {{-- Plus --}}
                  <button type="submit" name="action" value="plus"
                          class="qty-btn" title="Augmenter la quantité">
                    +
                  </button>
                </form>

                {{-- ── SUPPRIMER un article ── --}}
                <form method="POST" action="{{ route('panier.supprimer', $id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-suppr"
                          title="Supprimer cet article"
                          onclick="return confirm('Supprimer {{ addslashes($item['nom']) }} du panier ?')">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>

              </div>
            </div>
          @endforeach

          {{-- ── VIDER LE PANIER ── --}}
          <div style="display:flex;justify-content:space-between;
                      align-items:center;margin-top:6px;">
            <form method="POST" action="{{ route('panier.vider') }}">
              @csrf
              @method('DELETE')
              <button type="submit"
                      style="background:none;border:none;
                             color:var(--gris-doux);font-size:13px;
                             cursor:pointer;padding:8px 0;
                             display:flex;align-items:center;gap:6px;
                             transition:color 0.2s;"
                      onmouseover="this.style.color='var(--rouge-fes)'"
                      onmouseout="this.style.color='var(--gris-doux)'"
                      onclick="return confirm('Vider tout le panier ?')">
                <i class="bi bi-trash"></i>Vider le panier
              </button>
            </form>
            <a href="{{ route('catalogue.index') }}"
               style="font-size:13px;color:var(--gris-doux);
                      text-decoration:none;display:flex;align-items:center;gap:5px;">
              <i class="bi bi-arrow-left"></i>Continuer mes achats
            </a>
          </div>
        </div>

        {{-- ── RÉCAPITULATIF ────────────────────────────── --}}
        <div class="col-lg-4">
          <div class="recap-card">
            <h3 style="font-family:var(--font-serif);font-size:22px;margin-bottom:20px;">
              Récapitulatif
            </h3>

            @php
              $sousTotal = collect(session('panier', []))
                             ->sum(fn($i) => $i['prix'] * $i['quantite']);
              $tva       = round($sousTotal * 0.20, 2);
              $total     = round($sousTotal + $tva, 2);
            @endphp

            <div class="recap-row">
              <span style="color:var(--gris-doux);">Articles ({{ $nbArticles }})</span>
              <span>{{ number_format($sousTotal, 2) }} MAD</span>
            </div>
            <div class="recap-row">
              <span style="color:var(--gris-doux);">Sous-total HT</span>
              <span>{{ number_format($sousTotal, 2) }} MAD</span>
            </div>
            <div class="recap-row">
              <span style="color:var(--gris-doux);">TVA (20%)</span>
              <span>{{ number_format($tva, 2) }} MAD</span>
            </div>
            <div class="recap-row">
              <span style="color:var(--gris-doux);">Livraison</span>
              <span style="color:var(--vert-atlas);font-weight:600;">
                <i class="bi bi-truck me-1"></i>Calculée au checkout
              </span>
            </div>
            <div class="recap-row total">
              <span>Total TTC</span>
              <span>{{ number_format($total, 2) }} MAD</span>
            </div>

            {{-- Bouton commander --}}
            @auth
              <a href="{{ route('checkout.index') }}" class="btn-or"
                 style="width:100%;margin-top:20px;padding:14px;
                        display:block;text-align:center;font-size:15px;">
                <i class="bi bi-credit-card me-2"></i>Passer la commande
              </a>
            @else
              <a href="{{ route('login') }}" class="btn-or"
                 style="width:100%;margin-top:20px;padding:14px;
                        display:block;text-align:center;font-size:15px;">
                <i class="bi bi-person me-2"></i>Se connecter pour commander
              </a>
            @endauth

            {{-- Sécurité --}}
            <div style="text-align:center;margin-top:14px;
                        font-size:12px;color:var(--gris-doux);">
              <i class="bi bi-shield-check me-1 text-success"></i>
              Paiement 100% sécurisé
            </div>

            {{-- Modes de paiement acceptés --}}
            <div style="display:flex;justify-content:center;gap:8px;
                        margin-top:12px;flex-wrap:wrap;">
              @foreach(['💵 Livraison','💳 Carte','🏦 Virement'] as $mode)
                <span style="font-size:11px;background:var(--sable);
                             border-radius:6px;padding:4px 8px;
                             color:var(--gris-doux);">
                  {{ $mode }}
                </span>
              @endforeach
            </div>
          </div>
        </div>

      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
// Animation de suppression
document.querySelectorAll('.btn-suppr').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const item = this.closest('.panier-item');
        if (item) {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity    = '0.5';
            item.style.transform  = 'translateX(20px)';
        }
    });
});

// Feedback visuel sur + / −
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.style.transform  = 'scale(1.2)';
        this.style.background = 'var(--sable)';
        setTimeout(() => {
            this.style.transform  = 'scale(1)';
            this.style.background = 'white';
        }, 200);
    });
});
</script>
@endpush