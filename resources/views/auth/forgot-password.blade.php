{{-- ================================================================
     resources/views/auth/forgot-password.blade.php
================================================================ --}}
@extends('layouts.auth')
@section('title', 'Mot de passe oublié')

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
          <i class="bi bi-key" style="font-size:26px;color:#fff;"></i>
        </div>
        <h1>Mot de passe oublié</h1>
        <p>Saisissez votre email pour recevoir un lien de réinitialisation</p>
      </div>
      <div class="auth-body">
        @if(session('status'))
          <div class="alert-tissu success mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
          </div>
        @endif
        @if($errors->any())
          <div class="alert-tissu error mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
          </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label-tissu">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control-tissu" placeholder="votre@email.ma" required autofocus>
          </div>
          <button type="submit" class="btn-or w-100" style="padding:11px;">
            <i class="bi bi-envelope me-2"></i>Envoyer le lien de réinitialisation
          </button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:14px;color:var(--gris-doux);">
          <a href="{{ route('login') }}" style="color:var(--or-dark);">
            <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
