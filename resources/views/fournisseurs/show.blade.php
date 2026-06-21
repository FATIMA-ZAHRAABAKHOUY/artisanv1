@extends('layouts.app')
@section('title', $fournisseur->nom . ' — Fournisseur')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('fournisseurs.index') }}">Fournisseurs</a></li>
  <li class="breadcrumb-item active">{{ $fournisseur->nom }}</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="card-tissu mb-4" style="padding:28px;">
      <div style="display:flex;align-items:flex-start;gap:24px;flex-wrap:wrap;">
        @if($fournisseur->getLogoUrl())
          <img src="{{ $fournisseur->getLogoUrl() }}" alt="" style="width:88px;height:88px;border-radius:16px;object-fit:cover;">
        @else
          <div style="width:88px;height:88px;border-radius:16px;background:linear-gradient(135deg,var(--or),var(--or-dark));
                      display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;color:white;">
            {{ strtoupper(substr($fournisseur->nom, 0, 1)) }}
          </div>
        @endif
        <div style="flex:1;min-width:240px;">
          <h1 style="font-family:'Amiri',serif;font-size:28px;margin:0 0 6px;">{{ $fournisseur->nom }}</h1>
          <div style="font-size:14px;color:var(--or-dark);margin-bottom:12px;">{{ $fournisseur->getTypeLabel() }}@if($fournisseur->ville) · {{ $fournisseur->ville }}@endif</div>

          @if($fournisseur->note_moyenne)
            <div style="font-size:14px;color:var(--or-dark);margin-bottom:12px;">
              @for($i=1;$i<=5;$i++)
                <i class="bi bi-star{{ $i <= round($fournisseur->note_moyenne) ? '-fill' : '' }}"></i>
              @endfor
              <span style="color:var(--gris-doux);">({{ number_format($fournisseur->note_moyenne, 1) }}/5)</span>
            </div>
          @endif

          <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
            @if($fournisseur->email)
              <div><i class="bi bi-envelope me-2" style="color:var(--gris-doux);"></i><a href="mailto:{{ $fournisseur->email }}">{{ $fournisseur->email }}</a></div>
            @endif
            @if($fournisseur->telephone)
              <div><i class="bi bi-telephone me-2" style="color:var(--gris-doux);"></i><a href="tel:{{ $fournisseur->telephone }}">{{ $fournisseur->telephone }}</a></div>
            @endif
            @if($fournisseur->whatsapp)
              <div><i class="bi bi-whatsapp me-2" style="color:var(--vert-atlas);"></i>
                <a href="https://wa.me/{{ preg_replace('/\D/','',$fournisseur->whatsapp) }}" target="_blank">WhatsApp</a></div>
            @endif
            @if($fournisseur->site_web)
              <div><i class="bi bi-globe me-2" style="color:var(--indigo);"></i>
                <a href="{{ $fournisseur->site_web }}" target="_blank">{{ $fournisseur->site_web }}</a></div>
            @endif
            @if($fournisseur->adresse)
              <div><i class="bi bi-geo-alt me-2" style="color:var(--gris-doux);"></i>{{ $fournisseur->adresse }}</div>
            @endif
          </div>
        </div>

        <div style="min-width:200px;">
          @if($fournisseur->remise_cooperative > 0)
            <div style="background:var(--sable);border-radius:var(--radius);padding:16px;text-align:center;margin-bottom:12px;">
              <div style="font-size:28px;font-weight:700;color:var(--vert-atlas);">{{ $fournisseur->remise_cooperative }}%</div>
              <div style="font-size:12px;color:var(--gris-doux);">Remise coopérative</div>
            </div>
          @endif
          @if($fournisseur->delai_livraison_min !== null)
            <div style="background:var(--sable);border-radius:var(--radius);padding:12px 16px;font-size:13px;text-align:center;">
              <i class="bi bi-clock me-1"></i>
              Délai : {{ $fournisseur->delai_livraison_min }}–{{ $fournisseur->delai_livraison_max }} jours
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--sable-dark);">
      <button type="button" class="f-tab active" data-tab="materiaux" style="padding:10px 18px;border:none;background:none;font-weight:600;cursor:pointer;border-bottom:2px solid var(--or);margin-bottom:-2px;color:var(--or-dark);">
        Matériaux disponibles ({{ $fournisseur->materiaux->count() }})
      </button>
      <button type="button" class="f-tab" data-tab="outils" style="padding:10px 18px;border:none;background:none;font-weight:500;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--gris-doux);">
        Outils disponibles ({{ $fournisseur->outils->count() }})
      </button>
    </div>

    <div id="panel-materiaux">
      @forelse($fournisseur->materiaux as $fm)
        <div class="card-tissu mb-3" style="padding:18px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
              <div style="font-weight:600;">{{ $fm->nom_produit }}</div>
              <div style="font-size:13px;color:var(--gris-doux);">
                {{ $fm->materiau?->nom }} — {{ $fm->materiau?->formation?->titre }}
              </div>
            </div>
            <div style="text-align:right;">
              <div style="font-weight:700;color:var(--or-dark);">{{ number_format($fm->prix_unitaire, 2) }} MAD@if($fm->unite_prix)/{{ $fm->unite_prix }}@endif</div>
              <span class="badge-statut badge-{{ $fm->stock_disponible ? 'confirmed' : 'cancelled' }}" style="font-size:11px;">
                {{ $fm->stock_disponible ? 'En stock' : 'Rupture' }}
              </span>
              @if($fm->est_recommande)<span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>@endif
            </div>
          </div>
          @if($fm->url_produit)
            <a href="{{ $fm->url_produit }}" target="_blank" class="btn-outline-or btn btn-sm mt-2">Voir le produit</a>
          @endif
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucun matériau référencé.</p>
      @endforelse
    </div>

    <div id="panel-outils" style="display:none;">
      @forelse($fournisseur->outils as $fo)
        <div class="card-tissu mb-3" style="padding:18px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
              <div style="font-weight:600;">{{ $fo->nom_produit }}</div>
              <div style="font-size:13px;color:var(--gris-doux);">
                {{ $fo->outil?->nom }} — {{ $fo->outil?->formation?->titre }}
              </div>
            </div>
            <div style="text-align:right;">
              <div style="font-weight:700;color:var(--or-dark);">{{ number_format($fo->prix_unitaire, 2) }} MAD</div>
              <span class="badge-statut badge-{{ $fo->stock_disponible ? 'confirmed' : 'cancelled' }}" style="font-size:11px;">
                {{ $fo->stock_disponible ? 'En stock' : 'Rupture' }}
              </span>
              @if($fo->est_recommande)<span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>@endif
            </div>
          </div>
          @if($fo->url_produit)
            <a href="{{ $fo->url_produit }}" target="_blank" class="btn-outline-or btn btn-sm mt-2">Voir le produit</a>
          @endif
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucun outil référencé.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.f-tab').forEach(function (tab) {
  tab.addEventListener('click', function () {
    document.querySelectorAll('.f-tab').forEach(function (t) {
      t.classList.remove('active');
      t.style.borderBottomColor = 'transparent';
      t.style.color = 'var(--gris-doux)';
      t.style.fontWeight = '500';
    });
    tab.classList.add('active');
    tab.style.borderBottomColor = 'var(--or)';
    tab.style.color = 'var(--or-dark)';
    tab.style.fontWeight = '600';
    document.getElementById('panel-materiaux').style.display = tab.dataset.tab === 'materiaux' ? 'block' : 'none';
    document.getElementById('panel-outils').style.display = tab.dataset.tab === 'outils' ? 'block' : 'none';
  });
});
</script>
@endpush
