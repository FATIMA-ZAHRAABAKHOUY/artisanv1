@extends('layouts.app')
@section('title', isset($fournisseur) ? 'Modifier fournisseur — Admin' : 'Nouveau fournisseur — Admin')

@push('styles')
@include('admin.partials.layout-styles')
@endpush

@section('content')
@php $isEdit = isset($fournisseur); @endphp
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0 0 4px;">
                    {{ $isEdit ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                </h1>
                @if($isEdit)
                    <div style="font-size:13px;color:var(--gris-doux);">{{ $fournisseur->nom }}</div>
                @endif
            </div>
            <a href="{{ route('admin.fournisseurs.index') }}" class="btn-outline-or btn btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour à la liste
            </a>
        </div>

        @if($errors->any())
            <div class="alert-tissu danger mb-3">
                @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        @endif

        <div class="card-tissu" style="max-width:720px;">
            <form method="POST"
                  action="{{ $isEdit ? route('admin.fournisseurs.update', $fournisseur->id) : route('admin.fournisseurs.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-tissu">Nom *</label>
                        <input type="text" name="nom" class="form-control form-control-tissu"
                               value="{{ old('nom', $fournisseur->nom ?? '') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Statut *</label>
                        <select name="statut" class="form-select form-control-tissu" required>
                            @foreach(['actif'=>'Actif','inactif'=>'Inactif'] as $v=>$l)
                                <option value="{{ $v }}" {{ old('statut', $fournisseur->statut ?? 'actif')===$v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Type *</label>
                        <select name="type" class="form-select form-control-tissu" required>
                            @foreach(['local'=>'🏪 Local','national'=>'🚚 National','en_ligne'=>'🌐 En ligne'] as $v=>$l)
                                <option value="{{ $v }}" {{ old('type', $fournisseur->type ?? '')===$v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu" for="site_web">Site web</label>
                        <input type="url" name="site_web" id="site_web" class="form-control form-control-tissu"
                               value="{{ old('site_web', $fournisseur->site_web ?? '') }}" placeholder="https://…">
                    </div>
                    @if($isEdit)
                    <div class="col-md-6">
                        <label class="form-label-tissu" for="email">Email (contact)</label>
                        <input type="email" name="email" id="email" class="form-control form-control-tissu"
                               value="{{ old('email', $fournisseur->email ?? '') }}">
                    </div>
                    @endif
                    <div class="col-md-{{ $isEdit ? '3' : '6' }}">
                        <label class="form-label-tissu">Téléphone</label>
                        <input type="text" name="telephone" class="form-control form-control-tissu"
                               value="{{ old('telephone', $fournisseur->telephone ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-tissu">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control form-control-tissu"
                               value="{{ old('whatsapp', $fournisseur->whatsapp ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label-tissu">Adresse</label>
                        <input type="text" name="adresse" class="form-control form-control-tissu"
                               value="{{ old('adresse', $fournisseur->adresse ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Ville</label>
                        <input type="text" name="ville" class="form-control form-control-tissu"
                               value="{{ old('ville', $fournisseur->ville ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="form-control form-control-tissu" id="logoInput">
                        @if($isEdit && $fournisseur->getLogoUrl())
                            <img src="{{ $fournisseur->getLogoUrl() }}" id="logoPreview" alt=""
                                 style="margin-top:8px;width:64px;height:64px;border-radius:8px;object-fit:cover;">
                        @else
                            <img id="logoPreview" alt="" style="display:none;margin-top:8px;width:64px;height:64px;border-radius:8px;object-fit:cover;">
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Remise coopérative (%)</label>
                        <input type="number" name="remise_cooperative" step="0.01" min="0" max="100"
                               class="form-control form-control-tissu"
                               value="{{ old('remise_cooperative', $fournisseur->remise_cooperative ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Délai livraison min (jours)</label>
                        <input type="number" name="delai_livraison_min" min="0"
                               class="form-control form-control-tissu"
                               value="{{ old('delai_livraison_min', $fournisseur->delai_livraison_min ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Délai livraison max (jours)</label>
                        <input type="number" name="delai_livraison_max" min="0"
                               class="form-control form-control-tissu"
                               value="{{ old('delai_livraison_max', $fournisseur->delai_livraison_max ?? '') }}">
                    </div>

                    @if(!isset($fournisseur))
                    <div class="col-12">
                        <div style="background:var(--sable);border-radius:var(--radius);padding:18px 20px;margin:16px 0;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:0;">
                                <input type="checkbox" name="creer_acces" id="creerAcces" value="1"
                                       {{ old('creer_acces') ? 'checked' : '' }}
                                       style="width:18px;height:18px;accent-color:var(--or);">
                                <div>
                                    <div style="font-weight:600;font-size:14px;">
                                        Créer un accès de connexion pour ce fournisseur
                                    </div>
                                    <div style="font-size:12.5px;color:var(--gris-doux);margin-top:2px;">
                                        Permet au fournisseur de se connecter et gérer lui-même son catalogue.
                                        Laissez décoché si vous gérez ce fournisseur manuellement sans lui
                                        donner accès à la plateforme.
                                    </div>
                                </div>
                            </label>

                            <div id="accesFields" style="display:{{ old('creer_acces') ? 'block' : 'none' }};margin-top:16px;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-tissu">
                                            Email <span style="color:var(--rouge-fes);">*</span>
                                        </label>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                               class="form-control form-control-tissu" id="emailField">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-tissu">
                                            Mot de passe initial <span style="color:var(--rouge-fes);">*</span>
                                        </label>
                                        <input type="password" name="password" class="form-control form-control-tissu"
                                               id="passwordField" placeholder="Min. 8 caractères" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-12">
                        <div style="background:var(--sable);border-radius:var(--radius);padding:14px 18px;margin:16px 0;">
                            @if($fournisseur->user_id)
                                <div style="font-size:13.5px;color:var(--vert-atlas);">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Ce fournisseur a un accès de connexion ({{ $fournisseur->user?->email ?? '—' }})
                                </div>
                            @else
                                <div style="font-size:13.5px;color:var(--gris-doux);">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Ce fournisseur n'a pas d'accès de connexion (fiche gérée manuellement)
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn-or btn">
                        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le fournisseur' }}
                    </button>
                    <a href="{{ route('admin.fournisseurs.index') }}" class="btn-outline-or btn">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const typeSelect = document.querySelector('[name="type"]');
    const siteWebField = document.querySelector('[name="site_web"]');
    const label = document.querySelector('label[for="site_web"]');

    function toggleSiteWebRequired() {
        if (!typeSelect || !siteWebField || !label) return;
        if (typeSelect.value === 'en_ligne') {
            siteWebField.required = true;
            label.innerHTML = 'Site web <span style="color:var(--rouge-fes)">*</span>';
        } else {
            siteWebField.required = false;
            label.innerHTML = 'Site web';
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', toggleSiteWebRequired);
        toggleSiteWebRequired();
    }

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

    const creerAccesCheckbox = document.getElementById('creerAcces');
    const accesFields = document.getElementById('accesFields');
    const emailField = document.getElementById('emailField');
    const passwordField = document.getElementById('passwordField');

    if (creerAccesCheckbox && accesFields) {
        function toggleAccesFields() {
            const checked = creerAccesCheckbox.checked;
            accesFields.style.display = checked ? 'block' : 'none';
            if (emailField) emailField.required = checked;
            if (passwordField) passwordField.required = checked;
        }
        creerAccesCheckbox.addEventListener('change', toggleAccesFields);
        toggleAccesFields();
    }
})();
</script>
@endpush
