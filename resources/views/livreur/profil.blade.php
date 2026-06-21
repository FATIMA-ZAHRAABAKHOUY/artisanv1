@extends('layouts.app')
@section('title', 'Mon Profil — Livreur')

@section('content')
@include('livreur.header')

<div style="padding:32px 20px;max-width:600px;margin:0 auto;">

  <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('livreur.dashboard') }}" style="color:var(--gris-doux);text-decoration:none;font-size:14px;">
      <i class="bi bi-arrow-left"></i> Retour
    </a>
  </div>

  @if(session('success'))
    <div class="alert-tissu success mb-4">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div class="alert-tissu error mb-4">
      <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
    </div>
  @endif
  <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:24px;margin-bottom:16px;">
    <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:20px;">
      Informations personnelles
    </h3>
    <form method="POST" action="{{ route('livreur.profil.update') }}" enctype="multipart/form-data">
      @csrf @method('PUT')

      <div style="text-align:center;margin-bottom:20px;">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--or);
                    color:white;display:flex;align-items:center;justify-content:center;
                    font-size:28px;font-weight:700;margin:0 auto 10px;overflow:hidden;">
          @if(auth()->user()->avatar)
            <img src="{{ asset('storage/'.auth()->user()->avatar) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
          @else
            {{ strtoupper(substr(auth()->user()->prenom, 0, 1)) }}
          @endif
        </div>
        <input type="file" name="avatar" accept="image/*" class="form-control-tissu" style="max-width:240px;margin:0 auto;">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label-tissu">Nom</label>
          <input type="text" name="nom" value="{{ old('nom', auth()->user()->nom) }}" class="form-control-tissu" required>
        </div>
        <div class="col-6">
          <label class="form-label-tissu">Prénom</label>
          <input type="text" name="prenom" value="{{ old('prenom', auth()->user()->prenom) }}" class="form-control-tissu" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label-tissu">Téléphone</label>
        <input type="tel" name="telephone" value="{{ old('telephone', auth()->user()->telephone) }}" class="form-control-tissu" required>
      </div>

      <div class="mb-3">
        <label class="form-label-tissu">Ville</label>
        <input type="text" name="ville" value="{{ old('ville', auth()->user()->ville) }}" class="form-control-tissu">
      </div>

      <div class="mb-4">
        <label class="form-label-tissu">Adresse</label>
        <textarea name="adresse" class="form-control-tissu" rows="2">{{ old('adresse', auth()->user()->adresse) }}</textarea>
      </div>

      <button type="submit" class="btn-or" style="padding:11px 24px;">
        <i class="bi bi-check-circle me-2"></i>Enregistrer
      </button>
    </form>
  </div>

  <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:24px;">
    <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:20px;">
      Changer le mot de passe
    </h3>
    <form method="POST" action="{{ route('livreur.profil.password') }}">
      @csrf @method('PUT')
      <div class="mb-3">
        <label class="form-label-tissu">Mot de passe actuel</label>
        <input type="password" name="current_password" class="form-control-tissu" required>
      </div>
      <div class="mb-3">
        <label class="form-label-tissu">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control-tissu" required>
      </div>
      <div class="mb-4">
        <label class="form-label-tissu">Confirmer le nouveau mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control-tissu" required>
      </div>
      <button type="submit" class="btn-indigo" style="padding:11px 24px;color:white;">
        <i class="bi bi-shield-lock me-2"></i>Modifier le mot de passe
      </button>
    </form>
  </div>
</div>
@endsection
