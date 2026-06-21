@extends('layouts.app')
@section('title', isset($fournisseur) ? 'Modifier fournisseur — Admin' : 'Nouveau fournisseur — Admin')

@push('styles')
@include('admin.partials.layout-styles')
<style>
.form-section {
    border-top: 1px solid var(--sable-dark);
    padding-top: 20px;
    margin-top: 20px;
}
.form-section-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--gris-doux);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--sable-dark);
}
.logo-drop-zone {
    border: 2px dashed var(--sable-dark);
    border-radius: var(--radius);
    padding: 18px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--sable);
}
.logo-drop-zone:hover, .logo-drop-zone.drag-over {
    border-color: var(--or);
    background: rgba(155,74,58,0.04);
}
.logo-drop-zone input[type="file"] { display: none; }
.acces-box {
    border-radius: var(--radius);
    border: 1.5px solid var(--sable-dark);
    padding: 18px 20px;
    background: var(--sable);
}
.acces-box.has-acces {
    border-color: rgba(74,103,65,0.35);
    background: #f0fdf4;
}
</style>
@endpush

@section('content')
@php $isEdit = isset($fournisseur); @endphp
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        {{-- ── En-tête ───────────────────────────────────────────────────── --}}
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div class="section-header mb-0">
                <div class="section-icon">
                    <i class="bi {{ $isEdit ? 'bi-building-gear' : 'bi-building-add' }}"></i>
                </div>
                <div>
                    <h2>{{ $isEdit ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}</h2>
                    <p>{{ $isEdit ? $fournisseur->nom : 'Renseignez les informations du fournisseur' }}</p>
                </div>
            </div>
            <a href="{{ route('admin.fournisseurs.index') }}" class="btn btn-outline-or btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour à la liste
            </a>
        </div>

        {{-- ── Erreurs ───────────────────────────────────────────────────── --}}
        @if($errors->any())
            <div class="alert-tissu error mb-4">
                <i class="bi bi-exclamation-circle me-2"></i>
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- ── Formulaire ───────────────────────────────────────────────── --}}
        <div class="card-tissu" style="max-width:760px;padding:28px 32px;">
            <form method="POST"
                  action="{{ $isEdit ? route('admin.fournisseurs.update', $fournisseur->id) : route('admin.fournisseurs.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- ─ Identité ──────────────────────────────────────────── --}}
                <div class="form-section-title"><i class="bi bi-person-vcard"></i>Identité</div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-tissu">Nom <span style="color:var(--rouge-fes)">*</span></label>
                        <input type="text" name="nom" class="form-control form-control-tissu"
                               value="{{ old('nom', $fournisseur->nom ?? '') }}"
                               placeholder="Ex : Souk des tissus Fès" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Statut <span style="color:var(--rouge-fes)">*</span></label>
                        <select name="statut" class="form-select form-control-tissu" required>
                            @foreach(['actif' => 'Actif', 'inactif' => 'Inactif'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('statut', $fournisseur->statut ?? 'actif') === $v ? 'selected' : '' }}>
                                    {{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Type <span style="color:var(--rouge-fes)">*</span></label>
                        <select name="type" class="form-select form-control-tissu" required>
                            @foreach(['local' => '🏪 Local', 'national' => '🚚 National', 'en_ligne' => '🌐 En ligne'] as $v => $l)
                                <option value="{{ $v }}"
                                    {{ old('type', $fournisseur->type ?? '') === $v ? 'selected' : '' }}>
                                    {{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu" for="site_web">Site web</label>
                        <input type="url" name="site_web" id="site_web"
                               class="form-control form-control-tissu"
                               value="{{ old('site_web', $fournisseur->site_web ?? '') }}"
                               placeholder="https://…">
                    </div>
                </div>

                {{-- ─ Contact ───────────────────────────────────────────── --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-telephone"></i>Contact</div>
                    <div class="row g-3">
                        @if($isEdit)
                            <div class="col-md-12">
                                <label class="form-label-tissu" for="email">Email</label>
                                <input type="email" name="email" id="email"
                                       class="form-control form-control-tissu"
                                       value="{{ old('email', $fournisseur->email ?? '') }}"
                                       placeholder="contact@fournisseur.ma">
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label-tissu">Téléphone</label>
                            <div style="position:relative;">
                                <i class="bi bi-telephone"
                                   style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--gris-doux);font-size:14px;"></i>
                                <input type="text" name="telephone" class="form-control form-control-tissu"
                                       style="padding-left:36px;"
                                       value="{{ old('telephone', $fournisseur->telephone ?? '') }}"
                                       placeholder="+212 6XX-XXXXXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">WhatsApp</label>
                            <div style="position:relative;">
                                <i class="bi bi-whatsapp"
                                   style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#25D366;font-size:14px;"></i>
                                <input type="text" name="whatsapp" class="form-control form-control-tissu"
                                       style="padding-left:36px;"
                                       value="{{ old('whatsapp', $fournisseur->whatsapp ?? '') }}"
                                       placeholder="+212 6XX-XXXXXX">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─ Localisation ──────────────────────────────────────── --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-geo-alt"></i>Localisation</div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-tissu">Adresse</label>
                            <input type="text" name="adresse" class="form-control form-control-tissu"
                                   value="{{ old('adresse', $fournisseur->adresse ?? '') }}"
                                   placeholder="N° rue, quartier…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">Ville</label>
                            <input type="text" name="ville" class="form-control form-control-tissu"
                                   value="{{ old('ville', $fournisseur->ville ?? '') }}"
                                   placeholder="Ex : Fès, Marrakech…">
                        </div>
                    </div>
                </div>

                {{-- ─ Logo & Conditions ─────────────────────────────────── --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-image"></i>Logo & Conditions</div>
                    <div class="row g-3">

                        {{-- Logo --}}
                        <div class="col-md-6">
                            <label class="form-label-tissu">Logo</label>
                            <div class="logo-drop-zone" id="logoDropZone"
                                 onclick="document.getElementById('logoInput').click()">
                                <input type="file" name="logo" id="logoInput" accept="image/*">
                                @if($isEdit && $fournisseur->getLogoUrl())
                                    <img id="logoPreview" src="{{ $fournisseur->getLogoUrl() }}" alt=""
                                         style="width:64px;height:64px;border-radius:var(--radius-sm);
                                                object-fit:cover;border:1px solid var(--sable-dark);margin-bottom:8px;">
                                @else
                                    <img id="logoPreview" alt=""
                                         style="display:none;width:64px;height:64px;
                                                border-radius:var(--radius-sm);object-fit:cover;
                                                border:1px solid var(--sable-dark);margin-bottom:8px;">
                                @endif
                                <i class="bi bi-cloud-upload"
                                   style="font-size:22px;color:var(--or);display:block;margin-bottom:6px;"></i>
                                <div style="font-size:13px;font-weight:600;color:var(--texte);">
                                    Cliquer pour choisir
                                </div>
                                <div style="font-size:11.5px;color:var(--gris-doux);margin-top:2px;">
                                    JPG, PNG, WEBP — max 2 Mo
                                </div>
                            </div>
                        </div>

                        {{-- Remise + délais --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label-tissu">Remise coopérative (%)</label>
                                <input type="number" name="remise_cooperative"
                                       step="0.01" min="0" max="100"
                                       class="form-control form-control-tissu"
                                       value="{{ old('remise_cooperative', $fournisseur->remise_cooperative ?? '') }}"
                                       placeholder="0">
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label-tissu">Délai min (j.)</label>
                                    <input type="number" name="delai_livraison_min" min="0"
                                           class="form-control form-control-tissu"
                                           value="{{ old('delai_livraison_min', $fournisseur->delai_livraison_min ?? '') }}"
                                           placeholder="0">
                                </div>
                                <div class="col-6">
                                    <label class="form-label-tissu">Délai max (j.)</label>
                                    <input type="number" name="delai_livraison_max" min="0"
                                           class="form-control form-control-tissu"
                                           value="{{ old('delai_livraison_max', $fournisseur->delai_livraison_max ?? '') }}"
                                           placeholder="0">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ─ Accès plateforme ──────────────────────────────────── --}}
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-shield-lock"></i>Accès plateforme</div>

                    @if(!$isEdit)
                        {{-- Création : checkbox toggle --}}
                        <div class="acces-box">
                            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;margin-bottom:0;">
                                <input type="checkbox" name="creer_acces" id="creerAcces" value="1"
                                       {{ old('creer_acces') ? 'checked' : '' }}
                                       style="width:18px;height:18px;accent-color:var(--or);flex-shrink:0;margin-top:2px;">
                                <div>
                                    <div style="font-weight:600;font-size:14px;color:var(--texte);">
                                        Créer un accès de connexion
                                    </div>
                                    <div style="font-size:12.5px;color:var(--gris-doux);margin-top:2px;line-height:1.5;">
                                        Permet au fournisseur de gérer lui-même son catalogue.
                                        Laissez décoché si vous gérez ce fournisseur manuellement.
                                    </div>
                                </div>
                            </label>

                            <div id="accesFields"
                                 style="display:{{ old('creer_acces') ? 'block' : 'none' }};
                                        margin-top:16px;padding-top:16px;border-top:1px solid var(--sable-dark);">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-tissu">
                                            Email <span style="color:var(--rouge-fes)">*</span>
                                        </label>
                                        <input type="email" name="email" id="emailField"
                                               value="{{ old('email') }}"
                                               class="form-control form-control-tissu"
                                               placeholder="contact@fournisseur.ma">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-tissu">
                                            Mot de passe initial <span style="color:var(--rouge-fes)">*</span>
                                        </label>
                                        <input type="password" name="password" id="passwordField"
                                               class="form-control form-control-tissu"
                                               placeholder="Min. 8 caractères"
                                               autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- Édition : info accès actuel --}}
                        @if($fournisseur->user_id)
                            <div class="acces-box has-acces">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="width:36px;height:36px;border-radius:50%;background:rgba(74,103,65,0.15);
                                                 display:flex;align-items:center;justify-content:center;
                                                 color:var(--vert-atlas);font-size:16px;flex-shrink:0;">
                                        <i class="bi bi-person-check"></i>
                                    </span>
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;color:var(--vert-atlas);">
                                            Accès de connexion actif
                                        </div>
                                        <div style="font-size:12.5px;color:var(--gris-doux);">
                                            {{ $fournisseur->user?->email ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="acces-box">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="width:36px;height:36px;border-radius:50%;background:var(--sable-dark);
                                                 display:flex;align-items:center;justify-content:center;
                                                 color:var(--gris-doux);font-size:16px;flex-shrink:0;">
                                        <i class="bi bi-person-x"></i>
                                    </span>
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;color:var(--texte);">
                                            Aucun accès de connexion
                                        </div>
                                        <div style="font-size:12.5px;color:var(--gris-doux);">
                                            Ce fournisseur est géré manuellement.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- ─ Boutons ───────────────────────────────────────────── --}}
                <div class="mt-4 d-flex gap-2 align-items-center">
                    <button type="submit" class="btn btn-or">
                        <i class="bi {{ $isEdit ? 'bi-check-circle' : 'bi-plus-circle' }} me-2"></i>
                        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le fournisseur' }}
                    </button>
                    <a href="{{ route('admin.fournisseurs.index') }}" class="btn btn-outline-or">
                        Annuler
                    </a>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    /* ── Type → Site web requis ── */
    const typeSelect  = document.querySelector('[name="type"]');
    const siteWebField = document.querySelector('[name="site_web"]');
    const siteWebLabel = document.querySelector('label[for="site_web"]');

    function toggleSiteWebRequired() {
        if (!typeSelect || !siteWebField || !siteWebLabel) return;
        const required = typeSelect.value === 'en_ligne';
        siteWebField.required = required;
        siteWebLabel.innerHTML = required
            ? 'Site web <span style="color:var(--rouge-fes)">*</span>'
            : 'Site web';
    }
    if (typeSelect) {
        typeSelect.addEventListener('change', toggleSiteWebRequired);
        toggleSiteWebRequired();
    }

    /* ── Logo preview ── */
    const logoInput   = document.getElementById('logoInput');
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

    /* ── Créer accès toggle ── */
    const creerAcces = document.getElementById('creerAcces');
    const accesFields = document.getElementById('accesFields');
    const emailField  = document.getElementById('emailField');
    const passField   = document.getElementById('passwordField');

    if (creerAcces && accesFields) {
        function toggleAcces() {
            const on = creerAcces.checked;
            accesFields.style.display = on ? 'block' : 'none';
            if (emailField) emailField.required = on;
            if (passField)  passField.required  = on;
        }
        creerAcces.addEventListener('change', toggleAcces);
        toggleAcces();
    }
})();
</script>
@endpush
