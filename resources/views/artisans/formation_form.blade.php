@extends('layouts.app')
@section('title', isset($formation) ? 'Modifier la formation' : 'Créer une formation')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item"><a href="{{ route('artisan.formations') }}">Mes formations</a></li>
  <li class="breadcrumb-item active">{{ isset($formation) ? 'Modifier' : 'Créer' }}</li>
@endsection

@push('styles')
<style>
.img-preview-wrap {
    position: relative; border-radius: var(--radius); overflow: hidden;
    background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
    height: 220px; display: flex; align-items: center; justify-content: center;
}
.img-preview-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
.img-preview-wrap .img-placeholder {
    color: rgba(255,255,255,0.3); font-size: 52px;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 10px;
}
.img-preview-wrap .img-placeholder span {
    font-size: 13px; color: rgba(255,255,255,0.45); font-family: var(--font-sans);
}
.img-drop-zone {
    border: 2px dashed var(--sable-dark); border-radius: var(--radius);
    padding: 20px; text-align: center; cursor: pointer;
    transition: all 0.2s ease; background: var(--sable);
}
.img-drop-zone:hover, .img-drop-zone.drag-over {
    border-color: var(--or); background: rgba(155,74,58,0.05);
}
.img-drop-zone input[type="file"] { display: none; }
</style>
@endpush

@section('content')
<div class="page-wrap">
  <div class="container-xl" style="max-width:820px;">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-mortarboard"></i></div>
      <div>
        <h2>{{ isset($formation) ? 'Modifier la formation' : 'Créer une formation' }}</h2>
        <p>{{ isset($formation) ? 'Mettez à jour les informations de votre formation.' : 'Renseignez les informations de votre nouvelle formation.' }}</p>
      </div>
    </div>

    @if($errors->any())
      <div class="alert-tissu error mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
      </div>
    @endif

    <form method="POST"
          action="{{ isset($formation) ? route('artisan.formations.update', $formation->id) : route('artisan.formations.store') }}"
          enctype="multipart/form-data">
      @csrf
      @isset($formation) @method('PUT') @endisset

      <div class="row g-4">

        {{-- Left column: image --}}
        <div class="col-lg-4">
          <label class="form-label-tissu mb-2">Image de couverture</label>

          {{-- Preview --}}
          <div class="img-preview-wrap mb-3" id="imgPreviewWrap">
            @if(isset($formation) && $formation->image)
              <img src="{{ $formation->image_url }}" id="imgPreview" alt="Aperçu">
            @else
              <div class="img-placeholder" id="imgPlaceholder">
                <i class="bi bi-image"></i>
                <span>Aperçu de l'image</span>
              </div>
              <img src="" id="imgPreview" alt="Aperçu" style="display:none;">
            @endif
          </div>

          {{-- Drop zone --}}
          <div class="img-drop-zone" id="imgDropZone" onclick="document.getElementById('imageInput').click()">
            <input type="file" name="image" id="imageInput" accept="image/*">
            <i class="bi bi-cloud-upload" style="font-size:24px;color:var(--or);margin-bottom:8px;display:block;"></i>
            <div style="font-size:13px;font-weight:600;color:var(--texte);margin-bottom:4px;">
              Cliquer pour choisir une image
            </div>
            <div style="font-size:12px;color:var(--gris-doux);">JPG, PNG, WEBP — max 3 Mo</div>
          </div>

          @isset($formation)
            @if($formation->image)
              <div style="margin-top:10px;font-size:12px;color:var(--gris-doux);">
                <i class="bi bi-check-circle text-success me-1"></i>Image actuelle conservée si aucune nouvelle image sélectionnée.
              </div>
            @endif
          @endisset
        </div>

        {{-- Right column: fields --}}
        <div class="col-lg-8">

          <div class="mb-3">
            <label class="form-label-tissu">Titre de la formation <span style="color:var(--rouge-fes)">*</span></label>
            <input type="text" name="titre" value="{{ old('titre', $formation->titre ?? '') }}"
                   class="form-control-tissu" placeholder="Ex : Initiation à la broderie Fassi" required>
          </div>

          <div class="mb-3">
            <label class="form-label-tissu">Description</label>
            <textarea name="description" rows="4" class="form-control-tissu"
                      placeholder="Décrivez le contenu, les objectifs et le public cible…">{{ old('description', $formation->description ?? '') }}</textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label-tissu">Date de début <span style="color:var(--rouge-fes)">*</span></label>
              <input type="date" name="date_debut"
                     value="{{ old('date_debut', isset($formation) ? $formation->date_debut?->format('Y-m-d') : '') }}"
                     class="form-control-tissu" required>
            </div>
            <div class="col-6">
              <label class="form-label-tissu">Date de fin <span style="color:var(--rouge-fes)">*</span></label>
              <input type="date" name="date_fin"
                     value="{{ old('date_fin', isset($formation) ? $formation->date_fin?->format('Y-m-d') : '') }}"
                     class="form-control-tissu" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label-tissu">Prix (MAD) <span style="color:var(--rouge-fes)">*</span></label>
              <input type="number" name="prix" min="0" step="0.01"
                     value="{{ old('prix', $formation->prix ?? 0) }}"
                     class="form-control-tissu" placeholder="0 = Gratuit" required>
            </div>
            <div class="col-6">
              <label class="form-label-tissu">Places max <span style="color:var(--rouge-fes)">*</span></label>
              <input type="number" name="places_max" min="1" max="50"
                     value="{{ old('places_max', $formation->places_max ?? '') }}"
                     class="form-control-tissu" placeholder="Ex : 15" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label-tissu">Lieu</label>
            <input type="text" name="lieu"
                   value="{{ old('lieu', $formation->lieu ?? '') }}"
                   class="form-control-tissu" placeholder="Ex : Fès — Médina, Atelier de la coopérative">
          </div>

          @isset($formation)
            <div class="mb-4">
              <label class="form-label-tissu d-flex align-items-center gap-2">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $formation->is_active) ? 'checked' : '' }}
                       style="width:16px;height:16px;accent-color:var(--or);">
                Formation active (visible dans le catalogue public)
              </label>
            </div>
          @endisset

          <div class="d-flex gap-3 mt-2">
            <button type="submit" class="btn-or" style="padding:11px 28px;">
              <i class="bi bi-check-circle me-2"></i>
              {{ isset($formation) ? 'Enregistrer les modifications' : 'Créer la formation' }}
            </button>
            <a href="{{ route('artisan.formations') }}" class="btn-outline-or" style="padding:11px 20px;">
              Annuler
            </a>
          </div>

        </div>
      </div>
    </form>

  </div>
</div>
@endsection

@push('scripts')
<script>
const input    = document.getElementById('imageInput');
const preview  = document.getElementById('imgPreview');
const placeholder = document.getElementById('imgPlaceholder');
const dropZone = document.getElementById('imgDropZone');

input.addEventListener('change', () => showPreview(input.files[0]));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('drag-over');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    input.files = e.dataTransfer.files;
    showPreview(file);
  }
});

function showPreview(file) {
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
    if (placeholder) placeholder.style.display = 'none';
  };
  reader.readAsDataURL(file);
}
</script>
@endpush
