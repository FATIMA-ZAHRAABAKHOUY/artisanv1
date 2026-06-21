@extends('layouts.app')
@section('title', 'Mon profil — Espace Fournisseur')

@push('styles')
<style>
.fournisseur-page { background: var(--sable); padding: 32px 0 64px; min-height: calc(100vh - 120px); }
.readonly-field { background: var(--sable) !important; color: var(--gris-doux); cursor: not-allowed; }
</style>
@endpush

@section('content')
@include('fournisseur.partials.header')

<div class="fournisseur-page">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-person-circle"></i></div>
      <div>
        <h2>Mon profil</h2>
        <p>Informations de contact modifiables — le reste est géré par l'administrateur</p>
      </div>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-3">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert-tissu danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="card-tissu" style="max-width:640px;padding:24px;">
      <p style="font-size:13px;color:var(--gris-doux);margin-bottom:20px;">
        <i class="bi bi-info-circle me-1"></i>
        Les champs grisés sont gérés par l'administrateur de la coopérative.
      </p>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label-tissu">Nom (lecture seule)</label>
          <input type="text" class="form-control form-control-tissu readonly-field" value="{{ $fournisseur->nom }}" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label-tissu">Type (lecture seule)</label>
          <input type="text" class="form-control form-control-tissu readonly-field" value="{{ $fournisseur->getTypeLabel() }}" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label-tissu">Email (lecture seule)</label>
          <input type="email" class="form-control form-control-tissu readonly-field" value="{{ $fournisseur->email ?? auth()->user()->email }}" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label-tissu">Remise coopérative (lecture seule)</label>
          <input type="text" class="form-control form-control-tissu readonly-field"
                 value="{{ $fournisseur->remise_cooperative ? $fournisseur->remise_cooperative.' %' : '—' }}" disabled>
        </div>
      </div>

      <form method="POST" action="{{ route('fournisseur.profil.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label-tissu">Téléphone</label>
            <input type="text" name="telephone" class="form-control form-control-tissu"
                   value="{{ old('telephone', $fournisseur->telephone) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label-tissu">WhatsApp</label>
            <input type="text" name="whatsapp" class="form-control form-control-tissu"
                   value="{{ old('whatsapp', $fournisseur->whatsapp) }}">
          </div>
          <div class="col-12">
            <label class="form-label-tissu">Adresse</label>
            <input type="text" name="adresse" class="form-control form-control-tissu"
                   value="{{ old('adresse', $fournisseur->adresse) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label-tissu">Ville</label>
            <input type="text" name="ville" class="form-control form-control-tissu"
                   value="{{ old('ville', $fournisseur->ville) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label-tissu" for="site_web">
              Site web
              @if($fournisseur->type === 'en_ligne')<span style="color:var(--rouge-fes);">*</span>@endif
            </label>
            <input type="url" name="site_web" id="site_web" class="form-control form-control-tissu"
                   value="{{ old('site_web', $fournisseur->site_web) }}" placeholder="https://…"
                   @if($fournisseur->type === 'en_ligne') required @endif>
          </div>
          <div class="col-12">
            <label class="form-label-tissu">Logo</label>
            <input type="file" name="logo" accept="image/*" class="form-control form-control-tissu" id="logoInput">
            @if($fournisseur->getLogoUrl())
              <img src="{{ $fournisseur->getLogoUrl() }}" id="logoPreview" alt=""
                   style="margin-top:8px;width:72px;height:72px;border-radius:8px;object-fit:cover;">
            @else
              <img id="logoPreview" alt="" style="display:none;margin-top:8px;width:72px;height:72px;border-radius:8px;object-fit:cover;">
            @endif
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

@push('scripts')
<script>
(function () {
  const logoInput = document.getElementById('logoInput');
  const logoPreview = document.getElementById('logoPreview');
  if (logoInput && logoPreview) {
    logoInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        logoPreview.src = URL.createObjectURL(file);
        logoPreview.style.display = 'block';
      }
    });
  }
})();
</script>
@endpush
