{{-- ================================================================
     resources/views/auth/forgot-password.blade.php
================================================================ --}}
@extends('layouts.auth')
@section('title', 'Mot de passe oublié')

@section('content')
<div style="min-height:calc(100vh - 200px);display:flex;align-items:center;
            background:linear-gradient(135deg,var(--sable),var(--sable-dark));padding:60px 0;">
  <div class="container">
    <div style="background:white;border-radius:20px;box-shadow:var(--shadow-lg);
                max-width:440px;width:100%;margin:0 auto;overflow:hidden;">
      <div style="background:linear-gradient(135deg,var(--indigo),var(--or-dark));
                  padding:36px 40px;text-align:center;color:white;">
        <div style="
            width:64px;height:64px;border-radius:50%;
            background:rgba(255,255,255,0.18);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 14px;
        ">
          <i class="bi bi-key" style="font-size:30px;color:#fff;"></i>
        </div>
        <h1 style="font-family:'Amiri',serif;font-size:32px;letter-spacing:0.3px;margin:0 0 8px;">Mot de passe oublié</h1>
        <p style="color:rgba(255,255,255,0.80);font-size:15px;margin:0;">
          Saisissez votre email pour recevoir un lien de réinitialisation
        </p>
      </div>
      <div style="padding:40px;">
        @if(session('status'))
          <div class="alert-tissu success mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
          </div>
        @endif
        @if($errors->any())
          <div class="alert-tissu error mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
          </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
          @csrf
          <div class="mb-4">
            <label class="form-label-tissu">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control-tissu" placeholder="votre@email.ma" required autofocus>
          </div>
          <button type="submit" class="btn-or w-100" style="padding:13px;font-size:15px;">
            <i class="bi bi-envelope me-2"></i>Envoyer le lien de réinitialisation
          </button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--gris-doux);">
          <a href="{{ route('login') }}" style="color:var(--or-dark);">
            <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
