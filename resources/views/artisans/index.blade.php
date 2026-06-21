@extends('layouts.app')
@section('title', "Nos artisans — L'Âme du Fil")
@section('breadcrumb')
  <li class="breadcrumb-item active">Artisans</li>
@endsection

@push('styles')
<style>
.artisans-wrap { padding: 48px 0 80px; }
.artisan-card {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); padding: 28px;
    text-align: center; box-shadow: var(--shadow-sm);
    transition: all 0.3s ease; height: 100%;
}
.artisan-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-3px);
    border-color: var(--or);
}
.artisan-avatar {
    width: 88px; height: 88px; border-radius: 50%;
    background: linear-gradient(135deg, var(--or), var(--or-dark));
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 36px; font-weight: 700;
    margin: 0 auto 16px;
    border: 3px solid var(--sable-dark);
    overflow: hidden;
}
.artisan-avatar img { width:100%; height:100%; object-fit:cover; }
</style>
@endpush

@section('content')
<div class="artisans-wrap">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-people"></i></div>
      <div>
        <h2>Nos Artisans</h2>
        <p>Découvrez les maîtres artisans vérifiés de la coopérative</p>
      </div>
    </div>

    {{-- Filtre spécialité --}}
    <form method="GET" action="{{ route('artisans.index') }}"
          style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                 padding:16px 20px;margin-bottom:28px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
      <div>
        <label class="form-label-tissu">Spécialité</label>
        <input type="text" name="specialite" value="{{ request('specialite') }}"
               class="form-control-tissu" placeholder="Broderie, Tissage…" style="width:200px;">
      </div>
      <div>
        <label class="form-label-tissu">Région</label>
        <input type="text" name="region" value="{{ request('region') }}"
               class="form-control-tissu" placeholder="Fès, Azrou…" style="width:160px;">
      </div>
      <button type="submit" class="btn-or" style="padding:9px 20px;">
        <i class="bi bi-search me-1"></i>Rechercher
      </button>
      <a href="{{ route('artisans.index') }}"
         style="font-size:13px;color:var(--gris-doux);padding:9px 0;">
        Réinitialiser
      </a>
    </form>

    @if($artisans->isEmpty())
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:64px;margin-bottom:16px;">👨‍🎨</div>
        <h3 style="font-family:var(--font-serif);">Aucun artisan trouvé</h3>
      </div>
    @else
      <div class="row g-3">
        @foreach($artisans as $artisan)
          <div class="col-6 col-md-4 col-lg-3">
            <div class="artisan-card">
              <div class="artisan-avatar">
                @if($artisan->user?->avatar)
                  <img src="{{ asset('storage/'.$artisan->user->avatar) }}"
                       alt="{{ $artisan->user->nom_complet }}">
                @else
                  {{ substr($artisan->user?->prenom ?? 'A', 0, 1) }}
                @endif
              </div>

              <div style="font-family:var(--font-serif);font-size:17px;font-weight:700;margin-bottom:4px;">
                {{ $artisan->user?->nom_complet }}
              </div>
              <div style="font-size:13px;color:var(--or-dark);margin-bottom:6px;">
                {{ $artisan->specialite }}
              </div>
              <div style="font-size:12.5px;color:var(--gris-doux);margin-bottom:10px;">
                <i class="bi bi-geo-alt me-1"></i>{{ $artisan->user?->ville ?? 'Maroc' }}
              </div>

              {{-- Étoiles --}}
              <div style="color:var(--or);font-size:13px;margin-bottom:10px;">
                @for($s=1; $s<=5; $s++)
                  <i class="bi bi-star{{ $s <= round($artisan->note_moyenne) ? '-fill' : '' }}"></i>
                @endfor
                <span style="color:var(--gris-doux);font-size:12px;margin-left:4px;">
                  {{ number_format($artisan->note_moyenne, 1) }}
                </span>
              </div>

              <div style="font-size:12.5px;color:var(--gris-doux);margin-bottom:14px;">
                {{ $artisan->produits_count }} produit{{ $artisan->produits_count > 1 ? 's' : '' }}
              </div>

              <a href="{{ route('artisans.show', $artisan->id) }}"
                 class="btn-outline-or" style="display:block;padding:8px 18px;font-size:13px;">
                Voir le profil
              </a>
            </div>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-center mt-4">
        {{ $artisans->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
