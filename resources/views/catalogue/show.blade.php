@extends('layouts.app')
@section('title', $produit->nom . " — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('catalogue.index') }}">Catalogue</a></li>
  <li class="breadcrumb-item active">{{ Str::limit($produit->nom, 40) }}</li>
@endsection

@push('styles')
<style>
.produit-detail { padding: 48px 0 80px; }
.thumb-img {
    width: 80px; height: 80px; border-radius: 8px; overflow: hidden;
    border: 2px solid var(--sable-dark); cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; background: var(--sable);
}
.thumb-img.active { border-color: var(--or); }
.thumb-img img { width:100%; height:100%; object-fit:cover; }
.main-img {
    aspect-ratio: 1/1; background: var(--sable); border-radius: var(--radius);
    overflow: hidden; display: flex; align-items: center; justify-content: center;
    font-size: 100px;
}
.main-img img { width:100%; height:100%; object-fit:cover; }
.qty-btn {
    width: 36px; height: 36px; border-radius: 50%;
    border: 1.5px solid var(--sable-dark); background: white;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 18px; font-weight: 300; transition: all 0.2s;
}
.qty-btn:hover { border-color: var(--or); color: var(--or-dark); }
.qty-val {
    width: 48px; text-align: center; font-size: 16px;
    font-weight: 600; border: none; outline: none; background: none;
}
.avis-card {
    background: var(--sable); border-radius: var(--radius-sm);
    padding: 16px; margin-bottom: 12px;
}
.artisan-box {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 24px; margin-top: 24px;
}
</style>
@endpush

@section('content')
<div class="produit-detail">
  <div class="container-xl">
    <div class="row g-5">

      {{-- ── IMAGES ────────────────────────────────── --}}
      <div class="col-lg-5">
        <div class="main-img mb-3" id="mainImg">
          @if(!empty($produit->images[0]))
            <img src="{{ asset('storage/'.$produit->images[0]) }}"
                 alt="{{ $produit->nom }}" id="mainImgEl">
          @else
            🧵
          @endif
        </div>
        @if(count($produit->images ?? []) > 1)
          <div class="d-flex gap-2 flex-wrap">
            @foreach($produit->images as $i => $img)
              <div class="thumb-img {{ $i==0 ? 'active' : '' }}"
                   onclick="changeImg('{{ asset('storage/'.$img) }}', this)">
                <img src="{{ asset('storage/'.$img) }}" alt="">
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- ── INFOS ─────────────────────────────────── --}}
      <div class="col-lg-7">
        <div style="font-size:12px;color:var(--or-dark);text-transform:uppercase;
                    letter-spacing:1px;font-weight:600;margin-bottom:8px;">
          {{ $produit->categorie?->nom ?? 'Artisanal' }}
        </div>

        <h1 style="font-family:var(--font-serif);font-size:32px;margin-bottom:12px;">
          {{ $produit->nom }}
        </h1>

        {{-- Note & Artisan --}}
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
          <div style="color:var(--or);">
            @for($s=1;$s<=5;$s++)
              <i class="bi bi-star{{ $s <= round($noteMoyenne) ? '-fill' : '' }}"></i>
            @endfor
            <span style="color:var(--gris-doux);font-size:14px;margin-left:6px;">
              {{ $noteMoyenne }}/5 ({{ $produit->avis->count() }} avis)
            </span>
          </div>
          <span style="color:var(--sable-dark);">|</span>
          <a href="{{ route('artisans.show', $produit->artisan->id) }}"
             style="font-size:14px;color:var(--or-dark);">
            <i class="bi bi-person-circle me-1"></i>
            {{ $produit->artisan?->user?->nom_complet }}
          </a>
        </div>

        {{-- Prix --}}
        <div style="font-family:var(--font-serif);font-size:42px;color:var(--or-dark);
                    font-weight:700;margin-bottom:20px;line-height:1;">
          {{ number_format($produit->prix, 2) }}
          <span style="font-size:18px;font-weight:400;color:var(--gris-doux);">MAD</span>
        </div>

        {{-- Description --}}
        <p style="color:var(--gris-doux);line-height:1.8;margin-bottom:24px;">
          {{ $produit->description }}
        </p>

        {{-- Infos produit --}}
        <div class="row g-2 mb-4">
          @if($produit->poids)
          <div class="col-6">
            <div style="background:var(--sable);border-radius:8px;padding:12px 14px;">
              <div style="font-size:11px;color:var(--gris-doux);text-transform:uppercase;margin-bottom:3px;">Poids</div>
              <div style="font-weight:600;font-size:15px;">{{ $produit->poids }} kg</div>
            </div>
          </div>
          @endif
          @if($produit->dimensions)
          <div class="col-6">
            <div style="background:var(--sable);border-radius:8px;padding:12px 14px;">
              <div style="font-size:11px;color:var(--gris-doux);text-transform:uppercase;margin-bottom:3px;">Dimensions</div>
              <div style="font-weight:600;font-size:15px;">{{ $produit->dimensions }}</div>
            </div>
          </div>
          @endif
          <div class="col-6">
            <div style="background:var(--sable);border-radius:8px;padding:12px 14px;">
              <div style="font-size:11px;color:var(--gris-doux);text-transform:uppercase;margin-bottom:3px;">Stock</div>
              <div style="font-weight:600;font-size:15px;color:{{ $produit->stock > 0 ? 'var(--vert-atlas)' : 'var(--rouge-fes)' }};">
                {{ $produit->stock > 0 ? $produit->stock . ' disponible(s)' : 'Rupture de stock' }}
              </div>
            </div>
          </div>
        </div>

        {{-- Ajout panier --}}
        @if($produit->stock > 0)
          @auth
            <form method="POST" action="{{ route('panier.ajouter', $produit->id) }}"
                  class="d-flex align-items-center gap-3 mb-4 flex-wrap">
              @csrf
              <div class="d-flex align-items-center gap-2"
                   style="background:var(--sable);border-radius:30px;padding:6px 16px;">
                <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                <input type="number" name="quantite" id="qty" class="qty-val" value="1" min="1" max="{{ $produit->stock }}">
                <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
              </div>
              <button type="submit" class="btn-or" style="padding:12px 28px;font-size:15px;">
                <i class="bi bi-bag-plus me-2"></i>Ajouter au panier
              </button>
            </form>
          @else
            <a href="{{ route('login') }}" class="btn-or"
               style="padding:13px 28px;font-size:15px;display:inline-flex;align-items:center;gap:8px;">
              <i class="bi bi-bag-plus"></i>Connectez-vous pour acheter
            </a>
          @endauth
        @else
          <button class="btn-or" disabled style="padding:13px 28px;opacity:0.5;cursor:not-allowed;">
            <i class="bi bi-x-circle me-2"></i>Rupture de stock
          </button>
        @endif

        {{-- Artisan box --}}
        <div class="artisan-box">
          <div class="d-flex align-items-center gap-3">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--ame-terre),var(--ame-terre-dark));
                        display:flex;align-items:center;justify-content:center;font-size:22px;color:white;flex-shrink:0;">
              {{ substr($produit->artisan?->user?->prenom ?? 'A', 0, 1) }}
            </div>
            <div>
              <div style="font-weight:600;font-size:15px;">{{ $produit->artisan?->user?->nom_complet }}</div>
              <div style="font-size:13px;color:var(--or-dark);">{{ $produit->artisan?->specialite }}</div>
              <div style="color:var(--or);font-size:12px;">
                @for($s=1;$s<=5;$s++)
                  <i class="bi bi-star{{ $s <= round($produit->artisan->note_moyenne ?? 0) ? '-fill' : '' }}"></i>
                @endfor
              </div>
            </div>
            <a href="{{ route('artisans.show', $produit->artisan->id) }}"
               class="btn-outline-or ms-auto" style="font-size:13px;padding:7px 16px;">
              Voir profil
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- ── AVIS ──────────────────────────────────── --}}
    <div class="row mt-5 g-4">
      <div class="col-lg-8">
        <h2 style="font-family:var(--font-serif);font-size:24px;margin-bottom:24px;">
          Avis clients ({{ $produit->avis->count() }})
        </h2>

        @forelse($produit->avis as $avis)
          <div class="avis-card">
            <div class="d-flex justify-content-between mb-2">
              <div style="font-weight:600;font-size:14px;">
                {{ $avis->client?->nom_complet ?? 'Client anonyme' }}
              </div>
              <div>
                <span style="color:var(--or);font-size:13px;">
                  @for($s=1;$s<=5;$s++)
                    <i class="bi bi-star{{ $s <= $avis->note ? '-fill' : '' }}"></i>
                  @endfor
                </span>
                <span style="font-size:12px;color:var(--gris-doux);margin-left:8px;">
                  {{ $avis->created_at?->format('d/m/Y') }}
                </span>
              </div>
            </div>
            @if($avis->commentaire)
              <p style="margin:0;font-size:14px;color:var(--gris-doux);">{{ $avis->commentaire }}</p>
            @endif
          </div>
        @empty
          <p style="color:var(--gris-doux);">Aucun avis pour ce produit.</p>
        @endforelse

        {{-- Formulaire avis --}}
        @auth
          <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:24px;margin-top:24px;">
            <h3 style="font-family:var(--font-serif);font-size:18px;margin-bottom:16px;">
              Laisser un avis
            </h3>
            <form method="POST" action="{{ route('catalogue.avis', $produit->id) }}">
              @csrf
              <div class="mb-3">
                <label class="form-label-tissu">Note</label>
                <div class="d-flex gap-2">
                  @for($n=1;$n<=5;$n++)
                    <label style="cursor:pointer;">
                      <input type="radio" name="note" value="{{ $n }}" style="display:none;">
                      <span style="font-size:28px;color:var(--sable-dark);transition:color 0.1s;"
                            class="star-btn" data-val="{{ $n }}">★</span>
                    </label>
                  @endfor
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label-tissu">Commentaire (optionnel)</label>
                <textarea name="commentaire" class="form-control-tissu" rows="3"
                          placeholder="Partagez votre expérience…"></textarea>
              </div>
              <button type="submit" class="btn-or">
                <i class="bi bi-send me-2"></i>Publier mon avis
              </button>
            </form>
          </div>
        @endauth
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function changeImg(src, el) {
  document.getElementById('mainImgEl').src = src;
  document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}
function changeQty(delta) {
  const input = document.getElementById('qty');
  const max   = parseInt(input.getAttribute('max'));
  let v = parseInt(input.value) + delta;
  if (v < 1) v = 1;
  if (v > max) v = max;
  input.value = v;
}
// Étoiles interactives
document.querySelectorAll('.star-btn').forEach((star, i, all) => {
  star.addEventListener('mouseover', () => {
    all.forEach((s, j) => s.style.color = j <= i ? 'var(--or)' : 'var(--sable-dark)');
  });
  star.addEventListener('click', () => {
    all.forEach((s, j) => {
      s.style.color = j <= i ? 'var(--or)' : 'var(--sable-dark)';
    });
    star.closest('label').querySelector('input').checked = true;
  });
});
</script>
@endpush