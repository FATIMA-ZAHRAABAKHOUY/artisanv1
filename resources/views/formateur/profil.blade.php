@extends('layouts.app')
@section('title', 'Mon profil — Formateur')

@push('styles')
<style>
.formateur-page { background:var(--sable); padding:32px 0 64px; min-height:calc(100vh - 120px); }
</style>
@endpush

@section('content')
@include('formateur.partials.header')

<div class="formateur-page">
  <div class="container-xl" style="max-width:720px;">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
      <h2 style="font-family:'Amiri',serif;font-size:22px;margin:0;">Mon profil formateur</h2>
      <a href="{{ route('formateur.dashboard') }}" class="btn-outline-or btn btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Tableau de bord
      </a>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert-tissu danger mb-3">
        @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
      </div>
    @endif

    <div class="card-tissu" style="padding:24px;">
      <form method="POST" action="{{ route('formateur.profil.update') }}">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label-tissu">Spécialité</label>
            <input type="text" class="form-control form-control-tissu" value="{{ $formateur->specialite }}" disabled>
            <div style="font-size:11px;color:var(--gris-doux);margin-top:4px;">Géré par l'administrateur</div>
          </div>
          @if($formateur->est_externe)
          <div class="col-md-6">
            <label class="form-label-tissu">Organisme</label>
            <input type="text" class="form-control form-control-tissu" value="{{ $formateur->organisme ?? '—' }}" disabled>
            <div style="font-size:11px;color:var(--gris-doux);margin-top:4px;">Géré par l'administrateur</div>
          </div>
          @endif
          <div class="col-12">
            <label class="form-label-tissu">Biographie</label>
            <textarea name="biographie" rows="4" class="form-control form-control-tissu">{{ old('biographie', $formateur->biographie) }}</textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label-tissu">Diplômes</label>
            <textarea name="diplomes" rows="3" class="form-control form-control-tissu">{{ old('diplomes', $formateur->diplomes) }}</textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label-tissu">Langues</label>
            <input type="text" name="langues" class="form-control form-control-tissu"
                   value="{{ old('langues', $formateur->langues) }}" placeholder="Français, Arabe…">
          </div>
          @if($formateur->est_externe)
          <div class="col-md-6">
            <label class="form-label-tissu">Tarif journée (MAD)</label>
            <input type="number" name="tarif_journee" step="0.01" min="0" class="form-control form-control-tissu"
                   value="{{ old('tarif_journee', $formateur->tarif_journee) }}">
          </div>
          @endif
          <div class="col-12">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="is_disponible" value="1"
                     {{ old('is_disponible', $formateur->is_disponible) ? 'checked' : '' }}>
              Je suis disponible pour de nouvelles formations
            </label>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn-or btn">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
