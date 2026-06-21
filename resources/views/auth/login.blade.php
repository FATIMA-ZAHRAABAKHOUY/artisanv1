{{-- ================================================================
     resources/views/auth/login.blade.php
================================================================ --}}
@extends('layouts.auth')
@section('title', 'Connexion — Tissu Artisanal')

@push('styles')
<style>
.auth-wrap {
    height: 100vh;
    overflow-y: auto;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, var(--sable) 0%, var(--sable-dark) 100%);
    padding: 20px;
}
.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    max-width: 460px;
    width: 100%;
    margin: 0 auto;
}
.auth-header {
    background: linear-gradient(135deg, var(--indigo), var(--or-dark));
    padding: 28px 36px;
    text-align: center;
    color: white;
}
.auth-header h1 { font-family:'Amiri',serif; font-size:32px; letter-spacing:0.3px; margin:0 0 6px; }
.auth-header p  { color:rgba(255,255,255,0.80); font-size:15px; margin:0; }
.auth-body { padding: 28px 36px 32px; }
.auth-divider {
    display: flex; align-items: center; gap: 12px;
    color: var(--gris-doux); font-size: 13px; margin: 16px 0;
}
.auth-divider::before, .auth-divider::after {
    content:''; flex:1; height:1px; background:var(--sable-dark);
}
</style>
@endpush

@section('content')
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card">
      <div class="auth-header">
        <div style="
            width:56px;height:56px;border-radius:50%;
            background:rgba(255,255,255,0.18);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 10px;
        ">
          <i class="bi bi-scissors" style="font-size:26px;color:#fff;"></i>
        </div>
        <h1>Connexion</h1>
        <p>Accédez à votre espace Tissu Artisanal</p>
      </div>
      <div class="auth-body">
        @if($errors->any())
          <div class="alert-tissu error mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf

          <div class="mb-3">
            <label class="form-label-tissu">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control-tissu" placeholder="votre@email.ma" required autofocus>
          </div>

          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <label class="form-label-tissu">Mot de passe</label>
              <a href="{{ route('password.request') }}" style="font-size:13px;color:var(--or-dark);">
                Mot de passe oublié ?
              </a>
            </div>
            <input type="password" name="password"
                   class="form-control-tissu" placeholder="••••••••" required>
          </div>

          <div class="d-flex align-items-center gap-3 mb-3">
            <input type="checkbox" name="remember" id="remember" style="width:16px;height:16px;accent-color:var(--or);">
            <label for="remember" style="font-size:14px;color:var(--gris-doux);cursor:pointer;">Se souvenir de moi</label>
          </div>

          <button type="submit" class="btn-or w-100" style="padding:13px;">
            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
          </button>
        </form>

        <div class="auth-divider">ou</div>

        <p style="text-align:center;font-size:14px;color:var(--gris-doux);">
          Pas encore de compte ?
          <a href="{{ route('register') }}" style="color:var(--or-dark);font-weight:600;">
            Créer un compte
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection

