@extends('layouts.app')
@section('title', isset($formateur) ? 'Modifier formateur — Admin' : 'Nouveau formateur — Admin')

@push('styles')
@include('admin.partials.layout-styles')
@endpush

@section('content')
@php $isEdit = isset($formateur); @endphp
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0 0 4px;">
                    {{ $isEdit ? 'Modifier le formateur' : 'Nouveau formateur' }}
                </h1>
                @if($isEdit)
                    <div style="font-size:13px;color:var(--gris-doux);">{{ $formateur->specialite }}</div>
                @endif
            </div>
            <a href="{{ route('admin.formateurs.index') }}" class="btn-outline-or btn btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour à la liste
            </a>
        </div>

        @if($errors->any())
            <div class="alert-tissu danger mb-3">
                @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        @endif

        <div class="card-tissu" style="max-width:760px;">
            <form method="POST"
                  action="{{ $isEdit ? route('admin.formateurs.update', $formateur->id) : route('admin.formateurs.store') }}">
                @csrf
                @if($isEdit) @method('PUT') @endif

                @if(!$isEdit)
                <div style="background:var(--sable);border-radius:var(--radius);padding:18px 20px;margin-bottom:20px;">
                    <div style="font-weight:600;font-size:14px;margin-bottom:12px;">Type de formateur *</div>
                    <div class="d-flex flex-wrap gap-4">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="radio" name="est_externe" value="0" id="typeInterne"
                                   {{ old('est_externe', '0') === '0' || old('est_externe') === 0 ? 'checked' : '' }}>
                            <span>Interne (artisan existant)</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="radio" name="est_externe" value="1" id="typeExterne"
                                   {{ old('est_externe') === '1' || old('est_externe') === 1 || old('est_externe') === true ? 'checked' : '' }}>
                            <span>Externe</span>
                        </label>
                    </div>
                </div>

                <div id="interneFields" class="mb-3" style="display:{{ old('est_externe', '0') == '1' ? 'none' : 'block' }};">
                    <label class="form-label-tissu">Artisan vérifié *</label>
                    <select name="artisan_id" class="form-select form-control-tissu" id="artisanSelect">
                        <option value="">— Choisir un artisan —</option>
                        @foreach($artisansDisponibles ?? [] as $artisan)
                            <option value="{{ $artisan->id }}" {{ old('artisan_id') == $artisan->id ? 'selected' : '' }}>
                                {{ $artisan->user?->nom_complet }} — {{ $artisan->specialite }}
                            </option>
                        @endforeach
                    </select>
                    @if(empty($artisansDisponibles) || count($artisansDisponibles) === 0)
                        <p style="font-size:12px;color:var(--gris-doux);margin-top:6px;">
                            Aucun artisan vérifié disponible (tous ont déjà un profil formateur).
                        </p>
                    @endif
                </div>

                <div id="externeExtra" style="display:{{ old('est_externe') == '1' ? 'block' : 'none' }};">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-tissu">Organisme</label>
                            <input type="text" name="organisme" value="{{ old('organisme') }}"
                                   class="form-control form-control-tissu" placeholder="Ex: Institut des Arts Marocains">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-tissu">Tarif journée (MAD)</label>
                            <input type="number" name="tarif_journee" value="{{ old('tarif_journee') }}"
                                   class="form-control form-control-tissu" step="0.01" min="0">
                        </div>
                    </div>

                    <div style="background:var(--sable);border-radius:var(--radius);padding:18px 20px;margin-bottom:20px;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:0;">
                            <input type="checkbox" name="creer_acces" id="creerAcces" value="1"
                                   {{ old('creer_acces') ? 'checked' : '' }}
                                   style="width:18px;height:18px;accent-color:var(--or);">
                            <div>
                                <div style="font-weight:600;font-size:14px;">Créer un accès de connexion</div>
                                <div style="font-size:12.5px;color:var(--gris-doux);margin-top:2px;">
                                    Réservé aux formateurs externes. Les formateurs internes se connectent via leur compte artisan.
                                </div>
                            </div>
                        </label>
                        <div id="accesFields" style="display:{{ old('creer_acces') ? 'block' : 'none' }};margin-top:16px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-tissu">Nom *</label>
                                    <input type="text" name="nom" value="{{ old('nom') }}" class="form-control form-control-tissu" id="nomField">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-tissu">Prénom *</label>
                                    <input type="text" name="prenom" value="{{ old('prenom') }}" class="form-control form-control-tissu" id="prenomField">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-tissu">Email *</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-tissu" id="emailField">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-tissu">Mot de passe initial *</label>
                                    <input type="password" name="password" class="form-control form-control-tissu" id="passwordField" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div style="background:var(--sable);border-radius:var(--radius);padding:14px 18px;margin-bottom:20px;">
                    @if($formateur->est_externe)
                        <div style="font-size:13.5px;">
                            <span style="background:#dbeafe;color:#1e40af;border-radius:20px;padding:2px 8px;font-size:11px;">Externe</span>
                            @if($formateur->user_id)
                                <span style="color:var(--vert-atlas);margin-left:8px;">
                                    <i class="bi bi-check-circle"></i> Accès login ({{ $formateur->user?->email }})
                                </span>
                            @else
                                <span style="color:var(--gris-doux);margin-left:8px;">Sans accès login — {{ $formateur->organisme ?? 'organisme non renseigné' }}</span>
                            @endif
                        </div>
                    @else
                        <div style="font-size:13.5px;">
                            <span style="background:var(--sable-dark);border-radius:20px;padding:2px 8px;font-size:11px;">Interne</span>
                            <span style="margin-left:8px;">Artisan : <strong>{{ $formateur->artisan?->user?->nom_complet ?? '—' }}</strong></span>
                        </div>
                    @endif
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label-tissu">Spécialité *</label>
                        <input type="text" name="specialite" class="form-control form-control-tissu" required
                               value="{{ old('specialite', $formateur->specialite ?? '') }}"
                               placeholder="Ex: Tissage berbère, Broderie Fassi…">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-tissu">Expérience (années)</label>
                        <input type="number" name="experience_annees" min="0" class="form-control form-control-tissu"
                               value="{{ old('experience_annees', $formateur->experience_annees ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label-tissu">Biographie</label>
                        <textarea name="biographie" rows="3" class="form-control form-control-tissu"
                                  placeholder="Parcours, expertise…">{{ old('biographie', $formateur->biographie ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Diplômes</label>
                        <textarea name="diplomes" rows="2" class="form-control form-control-tissu">{{ old('diplomes', $formateur->diplomes ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Langues</label>
                        <input type="text" name="langues" class="form-control form-control-tissu"
                               value="{{ old('langues', $formateur->langues ?? '') }}"
                               placeholder="Français, Arabe, Amazigh…">
                    </div>
                    @if($isEdit && $formateur->est_externe)
                    <div class="col-md-6">
                        <label class="form-label-tissu">Organisme</label>
                        <input type="text" name="organisme" class="form-control form-control-tissu"
                               value="{{ old('organisme', $formateur->organisme ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-tissu">Tarif journée (MAD)</label>
                        <input type="number" name="tarif_journee" step="0.01" min="0" class="form-control form-control-tissu"
                               value="{{ old('tarif_journee', $formateur->tarif_journee ?? '') }}">
                    </div>
                    @endif
                    <div class="col-12">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="is_disponible" value="1"
                                   {{ old('is_disponible', $formateur->is_disponible ?? true) ? 'checked' : '' }}>
                            <span>Formateur disponible pour de nouvelles formations</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn-or btn">
                        {{ $isEdit ? 'Enregistrer' : 'Créer le formateur' }}
                    </button>
                    <a href="{{ route('admin.formateurs.index') }}" class="btn-outline-or btn">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const typeInterne = document.getElementById('typeInterne');
    const typeExterne = document.getElementById('typeExterne');
    const interneFields = document.getElementById('interneFields');
    const externeExtra = document.getElementById('externeExtra');
    const artisanSelect = document.getElementById('artisanSelect');
    const creerAcces = document.getElementById('creerAcces');
    const accesFields = document.getElementById('accesFields');

    function toggleTypeFields() {
        if (!typeInterne || !typeExterne) return;
        const externe = typeExterne.checked;
        if (interneFields) interneFields.style.display = externe ? 'none' : 'block';
        if (externeExtra) externeExtra.style.display = externe ? 'block' : 'none';
        if (artisanSelect) artisanSelect.required = !externe;
        toggleAccesFields();
    }

    function toggleAccesFields() {
        if (!creerAcces || !accesFields) return;
        const show = typeExterne && typeExterne.checked && creerAcces.checked;
        accesFields.style.display = show ? 'block' : 'none';
        ['nomField', 'prenomField', 'emailField', 'passwordField'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = show;
        });
    }

    typeInterne?.addEventListener('change', toggleTypeFields);
    typeExterne?.addEventListener('change', toggleTypeFields);
    creerAcces?.addEventListener('change', toggleAccesFields);

    toggleTypeFields();
})();
</script>
@endpush
