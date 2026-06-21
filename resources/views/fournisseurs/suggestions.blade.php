@extends('layouts.app')
@section('title', 'Où acheter le matériel — ' . $formation->titre)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('formations.index') }}">Formations</a></li>
  <li class="breadcrumb-item"><a href="{{ route('formations.show', $formation->id) }}">{{ $formation->titre }}</a></li>
  <li class="breadcrumb-item active">Où acheter</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl" style="max-width:900px;">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-shop"></i></div>
      <div>
        <h2>Où acheter le matériel</h2>
        <p>{{ $formation->titre }} — Fournisseurs recommandés par la coopérative</p>
      </div>
    </div>

    <div class="alert-tissu info mb-4">
      <i class="bi bi-info-circle me-2"></i>
      Les matériaux marqués « Fourni » sont déjà inclus. Ci-dessous, les suggestions pour ce que vous devez acheter vous-même.
    </div>

    {{-- MATÉRIAUX --}}
    <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:16px;">🧵 Matériaux</h3>
    @php $materiauxAcheter = $formation->materiaux->where('est_fourni', false); @endphp

    @forelse($materiauxAcheter as $mat)
      <div class="card-tissu mb-4" style="padding:20px;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
          <div>
            <div style="font-weight:700;font-size:16px;">{{ $mat->nom }}</div>
            <div style="font-size:13px;color:var(--gris-doux);">
              @if($mat->type){{ $mat->type }} · @endif
              @if($mat->quantite)<strong>{{ $mat->quantite }} {{ $mat->unite }}</strong>@endif
              @if($mat->couleur) · Couleur : {{ $mat->couleur }}@endif
            </div>
          </div>
        </div>

        @php $offres = $mat->fournisseurs->filter(fn($fm) => $fm->fournisseur && $fm->fournisseur->statut === 'actif'); @endphp

        @if($offres->count())
          <div class="row g-3">
            @foreach($offres as $fm)
              @php
                $url = $fm->url_produit ?: $fm->fournisseur->site_web;
              @endphp
              <div class="col-md-6">
                <div style="border:1px solid var(--sable-dark);border-radius:var(--radius);padding:14px;height:100%;">
                  <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    @if($fm->fournisseur->getLogoUrl())
                      <img src="{{ $fm->fournisseur->getLogoUrl() }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                    @else
                      <div style="width:36px;height:36px;border-radius:8px;background:var(--sable-dark);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--or-dark);">
                        {{ strtoupper(substr($fm->fournisseur->nom, 0, 1)) }}
                      </div>
                    @endif
                    <div>
                      <div style="font-weight:600;font-size:14px;">{{ $fm->fournisseur->nom }}</div>
                      <div style="font-size:12px;color:var(--gris-doux);">{{ $fm->fournisseur->getTypeLabel() }}</div>
                    </div>
                  </div>
                  <div style="font-size:14px;margin-bottom:6px;">{{ $fm->nom_produit }}</div>
                  <div style="font-weight:700;color:var(--or-dark);margin-bottom:8px;">
                    {{ number_format($fm->prix_unitaire, 2) }} MAD@if($fm->unite_prix)/{{ $fm->unite_prix }}@endif
                  </div>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
                    @if($fm->est_recommande)<span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>@endif
                    <span class="badge-statut badge-{{ $fm->stock_disponible ? 'confirmed' : 'cancelled' }}" style="font-size:11px;">
                      {{ $fm->stock_disponible ? 'En stock' : 'Rupture' }}
                    </span>
                  </div>
                  @if($url)
                    <button type="button" class="btn-indigo btn btn-sm w-100"
                            data-track-fournisseur
                            data-fournisseur-id="{{ $fm->fournisseur_id }}"
                            data-formation-id="{{ $formation->id }}"
                            data-type-objet="materiau"
                            data-objet-id="{{ $fm->id }}"
                            data-url="{{ $url }}">
                      Voir chez ce fournisseur <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </button>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p style="color:var(--gris-doux);font-size:14px;margin:0;">Aucun fournisseur référencé pour ce matériau.</p>
        @endif
      </div>
    @empty
      <p style="color:var(--gris-doux);margin-bottom:32px;">Tous les matériaux sont fournis par la coopérative.</p>
    @endforelse

    {{-- OUTILS --}}
    <h3 style="font-family:'Amiri',serif;font-size:20px;margin:32px 0 16px;">🔧 Outils</h3>
    @php $outilsAcheter = $formation->outils->where('est_fourni', false); @endphp

    @forelse($outilsAcheter as $outil)
      <div class="card-tissu mb-4" style="padding:20px;">
        <div style="margin-bottom:14px;">
          <div style="font-weight:700;font-size:16px;">{{ $outil->nom }}</div>
          @if($outil->quantite > 1)
            <div style="font-size:13px;color:var(--gris-doux);">Quantité : ×{{ $outil->quantite }}</div>
          @endif
          @if($outil->description)
            <p style="font-size:13px;color:var(--gris-doux);margin:6px 0 0;">{{ $outil->description }}</p>
          @endif
        </div>

        @php $offresO = $outil->fournisseurs->filter(fn($fo) => $fo->fournisseur && $fo->fournisseur->statut === 'actif'); @endphp

        @if($offresO->count())
          <div class="row g-3">
            @foreach($offresO as $fo)
              @php
                $url = $fo->url_produit ?: $fo->fournisseur->site_web;
              @endphp
              <div class="col-md-6">
                <div style="border:1px solid var(--sable-dark);border-radius:var(--radius);padding:14px;height:100%;">
                  <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    @if($fo->fournisseur->getLogoUrl())
                      <img src="{{ $fo->fournisseur->getLogoUrl() }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                    @else
                      <div style="width:36px;height:36px;border-radius:8px;background:var(--sable-dark);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--or-dark);">
                        {{ strtoupper(substr($fo->fournisseur->nom, 0, 1)) }}
                      </div>
                    @endif
                    <div>
                      <div style="font-weight:600;font-size:14px;">{{ $fo->fournisseur->nom }}</div>
                      <div style="font-size:12px;color:var(--gris-doux);">{{ $fo->fournisseur->getTypeLabel() }}</div>
                    </div>
                  </div>
                  <div style="font-size:14px;margin-bottom:6px;">{{ $fo->nom_produit }}</div>
                  <div style="font-weight:700;color:var(--or-dark);margin-bottom:8px;">{{ number_format($fo->prix_unitaire, 2) }} MAD</div>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
                    @if($fo->est_recommande)<span class="badge-statut badge-confirmed" style="font-size:11px;">⭐ Recommandé</span>@endif
                    <span class="badge-statut badge-{{ $fo->stock_disponible ? 'confirmed' : 'cancelled' }}" style="font-size:11px;">
                      {{ $fo->stock_disponible ? 'En stock' : 'Rupture' }}
                    </span>
                  </div>
                  @if($url)
                    <button type="button" class="btn-indigo btn btn-sm w-100"
                            data-track-fournisseur
                            data-fournisseur-id="{{ $fo->fournisseur_id }}"
                            data-formation-id="{{ $formation->id }}"
                            data-type-objet="outil"
                            data-objet-id="{{ $fo->id }}"
                            data-url="{{ $url }}">
                      Voir chez ce fournisseur <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </button>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p style="color:var(--gris-doux);font-size:14px;margin:0;">Aucun fournisseur référencé pour cet outil.</p>
        @endif
      </div>
    @empty
      <p style="color:var(--gris-doux);">Tous les outils sont fournis par la coopérative.</p>
    @endforelse

    <div class="mt-4">
      <a href="{{ route('formations.show', $formation->id) }}" class="btn-outline-or btn">
        <i class="bi bi-arrow-left me-1"></i>Retour à la formation
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-track-fournisseur]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    const url = this.dataset.url;
    fetch('/fournisseurs/' + this.dataset.fournisseurId + '/click', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        formation_id: parseInt(this.dataset.formationId, 10),
        type_objet: this.dataset.typeObjet,
        objet_id: parseInt(this.dataset.objetId, 10),
      }),
    }).catch(function () {});
    window.open(url, '_blank');
  });
});
</script>
@endpush
