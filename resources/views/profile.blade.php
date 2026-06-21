{{-- ================================================================
     resources/views/profile.blade.php
================================================================ --}}
@extends('layouts.app')
@section('title', 'Mon Profil')

@section('breadcrumb')
  <li class="breadcrumb-item active">Mon profil</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl" style="max-width:760px;">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-person-circle"></i></div>
      <div>
        <h2>Mon Profil</h2>
        <p>Gérez vos informations personnelles</p>
      </div>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      </div>
    @endif

    <div class="row g-4">
      {{-- Infos personnelles --}}
      <div class="col-lg-8">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:32px;">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:24px;
                     padding-bottom:12px;border-bottom:2px solid var(--sable-dark);">
            Informations personnelles
          </h3>
          <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label-tissu">Nom</label>
                <input type="text" name="nom" value="{{ old('nom', auth()->user()->nom) }}"
                       class="form-control-tissu" required>
              </div>
              <div class="col-6">
                <label class="form-label-tissu">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom', auth()->user()->prenom) }}"
                       class="form-control-tissu" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label-tissu">Email</label>
              <input type="email" value="{{ auth()->user()->email }}"
                     class="form-control-tissu" disabled
                     style="background:var(--sable);color:var(--gris-doux);">
              <small style="font-size:12px;color:var(--gris-doux);">L'email ne peut pas être modifié.</small>
            </div>

            <div class="mb-3">
              <label class="form-label-tissu">Téléphone</label>
              <input type="tel" name="telephone" value="{{ old('telephone', auth()->user()->telephone) }}"
                     class="form-control-tissu" placeholder="0661-XXXXXX">
            </div>

            <div class="mb-3">
              <label class="form-label-tissu">Adresse</label>
              <textarea name="adresse" class="form-control-tissu" rows="2"
                        placeholder="Votre adresse">{{ old('adresse', auth()->user()->adresse) }}</textarea>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-7">
                <label class="form-label-tissu">Ville</label>
                <input type="text" name="ville" value="{{ old('ville', auth()->user()->ville) }}"
                       class="form-control-tissu" placeholder="Casablanca">
              </div>
              <div class="col-5">
                <label class="form-label-tissu">Code postal</label>
                <input type="text" name="code_postal" value="{{ old('code_postal', auth()->user()->code_postal) }}"
                       class="form-control-tissu" placeholder="20000">
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label-tissu">Photo de profil</label>
              <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:64px;height:64px;border-radius:50%;
                            background:linear-gradient(135deg,var(--or),var(--or-dark));
                            display:flex;align-items:center;justify-content:center;
                            color:white;font-size:26px;font-weight:700;flex-shrink:0;">
                  @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}"
                         style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                  @else
                    {{ substr(auth()->user()->prenom, 0, 1) }}
                  @endif
                </div>
                <input type="file" name="avatar" accept="image/*" class="form-control-tissu"
                       style="max-width:300px;">
              </div>
            </div>

            <button type="submit" class="btn-or" style="padding:11px 28px;">
              <i class="bi bi-check-circle me-2"></i>Enregistrer les modifications
            </button>
          </form>
        </div>

        {{-- Changer mot de passe --}}
        <div id="parametres" style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:32px;margin-top:16px;">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:24px;
                     padding-bottom:12px;border-bottom:2px solid var(--sable-dark);">
            Changer le mot de passe
          </h3>
          <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')
            <div class="mb-3">
              <label class="form-label-tissu">Mot de passe actuel</label>
              <input type="password" name="current_password" class="form-control-tissu"
                     placeholder="••••••••" required>
            </div>
            <div class="mb-3">
              <label class="form-label-tissu">Nouveau mot de passe</label>
              <input type="password" name="password" class="form-control-tissu"
                     placeholder="Minimum 8 caractères" required>
            </div>
            <div class="mb-4">
              <label class="form-label-tissu">Confirmer le nouveau mot de passe</label>
              <input type="password" name="password_confirmation" class="form-control-tissu"
                     placeholder="Répétez le mot de passe" required>
            </div>
            <button type="submit" class="btn-indigo" style="padding:11px 24px;">
              <i class="bi bi-shield-lock me-2"></i>Modifier le mot de passe
            </button>
          </form>
        </div>
      </div>

      {{-- Résumé compte --}}
      <div class="col-lg-4">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:24px;text-align:center;">
          <div style="width:80px;height:80px;border-radius:50%;
                      background:linear-gradient(135deg,var(--or),var(--or-dark));
                      display:flex;align-items:center;justify-content:center;
                      color:white;font-size:32px;font-weight:700;
                      margin:0 auto 16px;">
            {{ substr(auth()->user()->prenom, 0, 1) }}
          </div>
          <div style="font-family:'Amiri',serif;font-size:20px;font-weight:700;margin-bottom:4px;">
            {{ auth()->user()->nom_complet }}
          </div>
          <div style="font-size:13px;color:var(--or-dark);margin-bottom:16px;">
            {{ ucfirst(auth()->user()->role) }}
          </div>
          <div style="font-size:13px;color:var(--gris-doux);padding-bottom:16px;
                      border-bottom:1px solid var(--sable-dark);margin-bottom:16px;">
            {{ auth()->user()->email }}
          </div>

          @if(auth()->user()->isArtisan())
            <a href="{{ route('artisan.dashboard') }}" class="btn-or w-100 mb-3" style="display:block;text-align:center;padding:10px 16px;">
              <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
          @endif

          @php
            $nbCommandes = auth()->user()->commandes()->count();
            $nbInscriptions = \App\Models\InscriptionFormation::where('apprenant_id', auth()->user()->id)->count();
          @endphp

          <div style="display:flex;justify-content:space-around;">
            <div style="text-align:center;">
              <div style="font-family:'Amiri',serif;font-size:24px;font-weight:700;color:var(--or-dark);">
                {{ $nbCommandes }}
              </div>
              <div style="font-size:12px;color:var(--gris-doux);">Commandes</div>
            </div>
            <div style="text-align:center;">
              <div style="font-family:'Amiri',serif;font-size:24px;font-weight:700;color:var(--indigo);">
                {{ $nbInscriptions }}
              </div>
              <div style="font-size:12px;color:var(--gris-doux);">Formations</div>
            </div>
          </div>
        </div>

        {{-- Liens rapides --}}
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:20px;margin-top:12px;">
          <h5 style="font-size:13px;font-weight:700;text-transform:uppercase;
                     letter-spacing:0.5px;color:var(--gris-doux);margin-bottom:12px;">
            Accès rapide
          </h5>
          @php
            $liensRapides = [
              ['bi-bag-check', 'Mes commandes', route('commandes.index')],
              ['bi-mortarboard', 'Mes formations', route('formations.mes-inscriptions')],
              ['bi-bell', 'Notifications', route('notifications.index')],
              ['bi-headset', 'Support', route('support.index')],
            ];
            if (auth()->user()->isArtisan()) {
              array_unshift($liensRapides, ['bi-speedometer2', 'Dashboard', route('artisan.dashboard')]);
            }
          @endphp
          @foreach($liensRapides as [$icon, $label, $url])
            <a href="{{ $url }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 0;
                      color:var(--texte);text-decoration:none;font-size:14px;
                      border-bottom:1px solid var(--sable-dark);">
              <i class="bi {{ $icon }}" style="color:var(--or);font-size:17px;width:20px;"></i>
              {{ $label }}
              <i class="bi bi-chevron-right" style="margin-left:auto;color:var(--gris-doux);font-size:12px;"></i>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection