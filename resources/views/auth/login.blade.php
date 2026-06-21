{{-- ================================================================
     resources/views/auth/login.blade.php
================================================================ --}}
@extends('layouts.app')
@section('title', 'Connexion — Tissu Artisanal')

@push('styles')
<style>
.auth-wrap {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, var(--sable) 0%, var(--sable-dark) 100%);
    padding: 60px 0;
}
.auth-card {
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    max-width: 480px;
    width: 100%;
    margin: 0 auto;
}
.auth-header {
    background: linear-gradient(135deg, var(--indigo), var(--or-dark));
    padding: 36px 40px;
    text-align: center;
    color: white;
}
.auth-header h1 { font-family:'Amiri',serif; font-size:28px; margin:0 0 6px; }
.auth-header p  { color:rgba(255,255,255,0.75); font-size:14px; margin:0; }
.auth-body { padding: 40px; }
.auth-divider {
    display: flex; align-items: center; gap: 12px;
    color: var(--gris-doux); font-size: 13px; margin: 20px 0;
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
        <div style="font-size:40px;margin-bottom:12px;">🧵</div>
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

          <div class="mb-4">
            <label class="form-label-tissu">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control-tissu" placeholder="votre@email.ma" required autofocus>
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
              <label class="form-label-tissu">Mot de passe</label>
              <a href="{{ route('password.request') }}" style="font-size:13px;color:var(--or-dark);">
                Mot de passe oublié ?
              </a>
            </div>
            <input type="password" name="password"
                   class="form-control-tissu" placeholder="••••••••" required>
          </div>

          <div class="d-flex align-items-center gap-3 mb-4">
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

