@extends('layouts.app')
@section('title', (isset($produit) ? 'Modifier produit' : 'Publier un produit')." — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item active">{{ isset($produit) ? 'Modifier' : 'Publier' }} un produit</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl" style="max-width:780px;">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-{{ isset($produit) ? 'pencil' : 'plus-circle' }}"></i></div>
      <div>
        <h2>{{ isset($produit) ? 'Modifier le produit' : 'Publier un produit' }}</h2>
        <p>Renseignez les informations de votre création artisanale</p>
      </div>
    </div>

    @if($errors->any())
      <div class="alert-tissu error mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST"
          action="{{ isset($produit) ? route('artisan.produits.update', $produit->id) : route('artisan.produits.store') }}"
          enctype="multipart/form-data">
      @csrf
      @if(isset($produit)) @method('PUT') @endif

      <div style="background:white;border-radius:var(--radius);
                  border:1px solid var(--sable-dark);padding:32px;margin-bottom:20px;">
        <h3 style="font-family:var(--font-serif);font-size:18px;margin-bottom:20px;
                   padding-bottom:12px;border-bottom:2px solid var(--sable-dark);">
          Informations générales
        </h3>
        <div class="mb-4">
          <label class="form-label-tissu">Nom du produit *</label>
          <input type="text" name="nom" value="{{ old('nom', $produit->nom ?? '') }}"
                 class="form-control-tissu" placeholder="Ex: Tapis Berbère Beni Ouarain…" required>
        </div>
        <div class="mb-4">
          <label class="form-label-tissu">Description *</label>
          <textarea name="description" class="form-control-tissu" rows="4"
                    placeholder="Décrivez votre création, les matériaux utilisés, la technique…" required>{{ old('description', $produit->description ?? '') }}</textarea>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <label class="form-label-tissu">Prix (MAD) *</label>
            <input type="number" name="prix" value="{{ old('prix', $produit->prix ?? '') }}"
                   class="form-control-tissu" placeholder="0.00" step="0.01" min="0" required>
          </div>
          <div class="col-6">
            <label class="form-label-tissu">Stock disponible *</label>
            <input type="number" name="stock" value="{{ old('stock', $produit->stock ?? 0) }}"
                   class="form-control-tissu" placeholder="0" min="0" required>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label-tissu">Catégorie</label>
          <select name="categorie_id" class="form-control-tissu">
            <option value="">-- Choisir une catégorie --</option>
            @foreach(\App\Models\Categorie::whereNull('parent_id')->get() as $cat)
              <option value="{{ $cat->id }}"
                {{ old('categorie_id', $produit->categorie_id ?? '') == $cat->id ? 'selected' : '' }}>
                {{ $cat->nom }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label-tissu">Poids (kg)</label>
            <input type="number" name="poids" value="{{ old('poids', $produit->poids ?? '') }}"
                   class="form-control-tissu" placeholder="0.00" step="0.001" min="0">
          </div>
          <div class="col-6">
            <label class="form-label-tissu">Dimensions</label>
            <input type="text" name="dimensions" value="{{ old('dimensions', $produit->dimensions ?? '') }}"
                   class="form-control-tissu" placeholder="Ex: 150×200cm">
          </div>
        </div>
      </div>

      {{-- Photos --}}
      <div style="background:white;border-radius:var(--radius);
                  border:1px solid var(--sable-dark);padding:32px;margin-bottom:20px;">
        <h3 style="font-family:var(--font-serif);font-size:18px;margin-bottom:20px;
                   padding-bottom:12px;border-bottom:2px solid var(--sable-dark);">
          Photos du produit
        </h3>

        {{-- Aperçu images existantes --}}
        @if(isset($produit) && !empty($produit->images))
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
            @foreach($produit->images as $img)
              <div style="width:80px;height:80px;border-radius:8px;overflow:hidden;
                          border:1px solid var(--sable-dark);">
                <img src="{{ asset('storage/'.$img) }}" alt=""
                     style="width:100%;height:100%;object-fit:cover;">
              </div>
            @endforeach
          </div>
        @endif

        <div style="border:2px dashed var(--sable-dark);border-radius:var(--radius);
                    padding:32px;text-align:center;cursor:pointer;transition:all 0.2s;"
             onclick="document.getElementById('imagesInput').click()"
             onmouseover="this.style.borderColor='var(--or)'"
             onmouseout="this.style.borderColor='var(--sable-dark)'">
          <div style="font-size:36px;margin-bottom:10px;">📸</div>
          <div style="font-weight:500;margin-bottom:4px;">
            Cliquez pour ajouter des photos
          </div>
          <div style="font-size:13px;color:var(--gris-doux);">
            JPG, PNG, WebP — Max {{ $maxUploadMo ?? '2,0' }} Mo par photo — Jusqu'à 5 photos
          </div>
          <input type="file" id="imagesInput" name="images[]"
                 multiple accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                 data-max-bytes="{{ $maxUploadBytes ?? 2097152 }}"
                 style="display:none;"
                 onchange="previewImages(this)">
        </div>
        <div id="imagePreview" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;"></div>
      </div>

      {{-- Statut --}}
      @if(isset($produit))
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:24px;margin-bottom:20px;">
          <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $produit->is_active ?? true) ? 'checked' : '' }}
                   style="width:20px;height:20px;accent-color:var(--or);">
            <div>
              <div style="font-weight:600;font-size:15px;">Produit actif et visible</div>
              <div style="font-size:13px;color:var(--gris-doux);">
                Décochez pour masquer temporairement ce produit du catalogue
              </div>
            </div>
          </label>
        </div>
      @endif

      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <a href="{{ route('artisan.dashboard') }}" class="btn-outline-or" style="padding:11px 24px;">
          Annuler
        </a>
        <button type="submit" class="btn-or" style="padding:11px 28px;font-size:15px;">
          <i class="bi bi-check-circle me-2"></i>
          {{ isset($produit) ? 'Enregistrer les modifications' : 'Publier le produit' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function previewImages(input) {
  const preview = document.getElementById('imagePreview');
  preview.innerHTML = '';
  const maxBytes = parseInt(input.dataset.maxBytes || '2097152', 10);
  const maxMo = (maxBytes / (1024 * 1024)).toLocaleString('fr-FR', { maximumFractionDigits: 1 });

  Array.from(input.files).slice(0, 5).forEach(file => {
    if (file.size > maxBytes) {
      alert(`"${file.name}" dépasse ${maxMo} Mo. Compressez l'image ou choisissez un fichier plus petit.`);
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.style.cssText = 'width:80px;height:80px;border-radius:8px;overflow:hidden;border:2px solid var(--or);';
      div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}
</script>
@endpush