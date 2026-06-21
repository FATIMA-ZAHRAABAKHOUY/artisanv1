@extends('layouts.app')
@section('title', 'Espace Fournisseur — Tissu Artisanal')

@push('styles')
<style>
.kpi-card-fourn {
    background: white;
    border-radius: var(--radius);
    border: 1px solid var(--sable-dark);
    padding: 18px;
    border-top: 3px solid var(--or);
    box-shadow: var(--shadow-sm);
}
.kpi-card-fourn.indigo { border-top-color: var(--indigo); }
.kpi-card-fourn.green  { border-top-color: var(--vert-atlas); }
.kpi-card-fourn.red    { border-top-color: var(--rouge-fes); }
.kpi-val-fourn {
    font-family: 'Amiri', serif;
    font-size: 28px;
    font-weight: 700;
}
.kpi-lbl-fourn {
    font-size: 12px;
    color: var(--gris-doux);
    margin-top: 4px;
}
</style>
@endpush

@section('content')
@include('fournisseur.partials.header')

<div style="padding: 32px 24px; max-width: 1100px; margin: 0 auto;">

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

  <div style="margin-bottom: 24px;">
    <h2 style="font-family: 'Amiri', serif; font-size: 24px; margin-bottom: 4px;">
      Bonjour, {{ $fournisseur->nom }}
    </h2>
    <p style="color: var(--gris-doux); font-size: 14px;">
      Gérez votre catalogue de matériaux et outils pour la coopérative
    </p>
  </div>

  @if($fournisseur->type === 'en_ligne' && empty($fournisseur->site_web))
    <div style="background: #fef3c7; border: 1.5px solid #f59e0b; border-radius: var(--radius);
                padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
      <i class="bi bi-exclamation-triangle" style="font-size: 20px; color: #f59e0b;"></i>
      <div style="flex: 1;">
        <div style="font-weight: 600; font-size: 13.5px;">Profil incomplet</div>
        <div style="font-size: 12.5px; color: var(--gris-doux);">
          Un fournisseur en ligne doit renseigner son site web.
        </div>
      </div>
      <a href="{{ route('fournisseur.profil') }}" class="btn-or" style="padding: 7px 16px; font-size: 13px;">
        Compléter
      </a>
    </div>
  @endif

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="kpi-card-fourn">
        <div class="kpi-val-fourn">{{ $nbMateriaux }}</div>
        <div class="kpi-lbl-fourn">Matériaux référencés</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-fourn indigo">
        <div class="kpi-val-fourn">{{ $nbOutils }}</div>
        <div class="kpi-lbl-fourn">Outils référencés</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-fourn green">
        <div class="kpi-val-fourn" style="color: var(--vert-atlas);">{{ $nbClics }}</div>
        <div class="kpi-lbl-fourn">Clics reçus</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-fourn red">
        <div class="kpi-val-fourn" style="color: var(--rouge-fes);">{{ $nbAchats }}</div>
        <div class="kpi-lbl-fourn">Achats confirmés</div>
      </div>
    </div>
  </div>

  <div style="margin-bottom: 24px;">
    <a href="{{ route('fournisseur.produits') }}" class="btn-or" style="padding: 12px 24px; font-size: 14px;">
      <i class="bi bi-box-seam me-2"></i>Gérer mon catalogue de produits
    </a>
    <a href="{{ route('fournisseur.profil') }}" class="btn-outline-or" style="padding: 12px 24px; font-size: 14px; margin-left: 10px;">
      <i class="bi bi-person-gear me-2"></i>Modifier mon profil
    </a>
  </div>

  <div class="card-tissu" style="padding: 20px;">
    <h3 style="font-family: 'Amiri', serif; font-size: 17px; margin-bottom: 16px;">
      Dernières consultations
    </h3>

    @forelse($recentActivity as $activity)
      <div style="display: flex; align-items: center; justify-content: space-between;
                  padding: 10px 0; border-bottom: 1px solid var(--sable-dark); flex-wrap: wrap; gap: 8px;">
        <div>
          <div style="font-size: 13.5px; font-weight: 500;">
            {{ $activity->formation?->titre ?? 'Formation supprimée' }}
          </div>
          <div style="font-size: 12px; color: var(--gris-doux);">
            {{ $activity->type_objet === 'materiau' ? 'Matériau consulté' : 'Outil consulté' }}
          </div>
        </div>
        <div style="font-size: 12px; color: var(--gris-doux);">
          {{ $activity->created_at?->diffForHumans() ?? '—' }}
        </div>
      </div>
    @empty
      <p style="color: var(--gris-doux); font-size: 13.5px; text-align: center; padding: 20px 0;">
        Aucune consultation récente. Vos produits apparaîtront ici dès que des apprenants
        consulteront vos offres lors de leurs formations.
      </p>
    @endforelse
  </div>

</div>
@endsection
