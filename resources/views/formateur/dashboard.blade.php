@extends('layouts.app')
@section('title', 'Espace Formateur — Tissu Artisanal')

@push('styles')
<style>
.kpi-card-form {
    background: white; border-radius: var(--radius); border: 1px solid var(--sable-dark);
    padding: 18px; border-top: 3px solid var(--or); box-shadow: var(--shadow-sm); height: 100%;
}
.kpi-card-form.indigo { border-top-color: var(--indigo); }
.kpi-card-form.green  { border-top-color: var(--vert-atlas); }
.kpi-val-form { font-family: 'Amiri', serif; font-size: 26px; font-weight: 700; }
.kpi-lbl-form { font-size: 12px; color: var(--gris-doux); margin-top: 4px; }
</style>
@endpush

@section('content')
@include('formateur.partials.header')

<div style="padding:32px 24px;max-width:1100px;margin:0 auto;">

  @if(session('success'))
    <div class="alert-tissu success mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
  @endif

  <div style="margin-bottom:24px;">
    <h2 style="font-family:'Amiri',serif;font-size:24px;margin-bottom:4px;">
      Bonjour, {{ auth()->user()->prenom }}
    </h2>
    <p style="color:var(--gris-doux);font-size:14px;">
      Consultez les formations auxquelles vous êtes assigné(e)
    </p>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="kpi-card-form">
        <div class="kpi-val-form">{{ $nbFormationsActives }}</div>
        <div class="kpi-lbl-form">Formations actives</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-form indigo">
        <div class="kpi-val-form">{{ $nbTotalInscrits }}</div>
        <div class="kpi-lbl-form">Inscrits en cours</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-form green">
        <div class="kpi-val-form" style="font-size:16px;line-height:1.3;padding-top:4px;">{{ $formateur->specialite }}</div>
        <div class="kpi-lbl-form">Spécialité</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="kpi-card-form">
        <div class="kpi-val-form" style="font-size:18px;">
          @if($formateur->tarif_journee)
            {{ number_format($formateur->tarif_journee, 0) }} MAD
          @else
            —
          @endif
        </div>
        <div class="kpi-lbl-form">Tarif / jour</div>
      </div>
    </div>
  </div>

  <div class="mb-3">
    <a href="{{ route('formateur.profil') }}" class="btn-outline-or btn btn-sm">
      <i class="bi bi-person-gear me-1"></i>Modifier mon profil
    </a>
  </div>

  <div class="card-tissu" style="padding:20px;">
    <h3 style="font-family:'Amiri',serif;font-size:17px;margin-bottom:16px;">Mes formations</h3>

    @forelse($formations as $formation)
      @php
        $role = $formation->pivot->role ?? 'intervenant';
        $roleLabels = ['principal'=>'Principal','assistant'=>'Assistant','intervenant'=>'Intervenant'];
      @endphp
      <div style="border:1px solid var(--sable-dark);border-radius:var(--radius);padding:16px;margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
          <div style="font-weight:700;font-size:16px;">{{ $formation->titre }}</div>
          <span class="badge-statut badge-confirmed">{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
        </div>
        <div style="font-size:13px;color:var(--gris-doux);margin-bottom:8px;">
          Organisateur : {{ $formation->artisan?->user?->nom_complet ?? '—' }}
          @if($formation->lieu) · {{ $formation->lieu }} @endif
        </div>
        <div style="font-size:13px;color:var(--gris-doux);">
          @if($formation->date_debut)
            {{ $formation->date_debut->format('d/m/Y') }}
            @if($formation->date_fin) — {{ $formation->date_fin->format('d/m/Y') }} @endif
          @endif
          · <strong>{{ $formation->inscrits_actifs }}</strong> inscrit(s) actif(s)
          @if($formation->is_active)
            <span style="color:var(--vert-atlas);">· Active</span>
          @else
            <span style="color:var(--gris-doux);">· Inactive</span>
          @endif
        </div>
      </div>
    @empty
      <p style="color:var(--gris-doux);text-align:center;padding:24px 0;">
        Aucune formation assignée pour le moment. L'administrateur vous ajoutera aux prochaines sessions.
      </p>
    @endforelse
  </div>
</div>
@endsection
