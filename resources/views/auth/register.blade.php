@extends('layouts.app')
@section('title', 'Inscription — Tissu Artisanal')

@push('styles')
<style>
.auth-wrap {
    min-height: calc(100vh - 200px);
    display: flex; align-items: center;
    background: linear-gradient(135deg, var(--sable) 0%, var(--sable-dark) 100%);
    padding: 60px 0;
}
.auth-card {
    background: white; border-radius: 20px;
    box-shadow: var(--shadow-lg); overflow: hidden;
    max-width: 720px; width: 100%; margin: 0 auto;
}
.auth-header {
    background: linear-gradient(135deg, var(--indigo), var(--or-dark));
    padding: 36px 40px; text-align: center; color: white;
}
.auth-header h1 { font-family:'Amiri',serif; font-size:28px; margin:0 0 6px; }
.auth-header p  { color:rgba(255,255,255,0.75); font-size:14px; margin:0; }
.auth-body { padding: 40px; }
.role-card {
    border: 2px solid var(--sable-dark); border-radius: 10px;
    padding: 16px 12px; text-align: center; cursor: pointer;
    transition: all 0.2s ease;
}
.role-card:hover { border-color: var(--or); background: var(--sable); }
.role-card input[type="radio"] { display: none; }
.role-card.selected { border-color: var(--or); background: rgba(200,145,58,0.07); }
.role-card .role-icon { font-size: 28px; display: block; margin-bottom: 6px; }
.role-card .role-name { font-size: 13px; font-weight: 600; color: var(--texte); }
#roleCards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 8px;
}
</style>
@endpush

@section('content')
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card">
      <div class="auth-header">
        <div style="font-size:40px;margin-bottom:12px;">🌟</div>
        <h1>Créer un compte</h1>
        <p>Rejoignez la Coopérative de Tissu Artisanal Marocain</p>
      </div>
      <div class="auth-body">

        @if($errors->any())
          <div class="alert-tissu error mb-4">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
          @csrf

          {{-- Rôle --}}
          <div class="mb-4">
            <label class="form-label-tissu mb-2">Je suis…</label>
            <div id="roleCards">
              @foreach([
                ['client','Client','🛒','Acheter des produits artisanaux'],
                ['artisan','Artisan','🧵','Vendre mes créations'],
                ['apprenant','Apprenant','🎓','Suivre des formations'],
                ['livreur','Livreur','bi-truck','Livrer les commandes'],
              ] as [$val,$nom,$icon,$desc])
              <label class="role-card {{ old('role')==$val ? 'selected' : ($val=='client'&&!old('role') ? 'selected':'') }}">
                <input type="radio" name="role" value="{{ $val }}"
                       {{ old('role',$val=='client'?'client':'') == $val ? 'checked' : '' }}>
                @if(str_starts_with($icon, 'bi-'))
                  <span class="role-icon"><i class="bi {{ $icon }}" style="font-size:28px;color:var(--or-dark);"></i></span>
                @else
                  <span class="role-icon">{{ $icon }}</span>
                @endif
                <div class="role-name">{{ $nom }}</div>
                <div style="font-size:11px;color:var(--gris-doux);margin-top:3px;">{{ $desc }}</div>
              </label>
              @endforeach
            </div>
          </div>

          {{-- Nom & Prénom --}}
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label-tissu">Nom</label>
              <input type="text" name="nom" value="{{ old('nom') }}"
                     class="form-control-tissu" placeholder="Nom de famille" required>
            </div>
            <div class="col-6">
              <label class="form-label-tissu">Prénom</label>
              <input type="text" name="prenom" value="{{ old('prenom') }}"
                     class="form-control-tissu" placeholder="Prénom" required>
            </div>
          </div>

          {{-- Email --}}
          <div class="mb-3">
            <label class="form-label-tissu">Adresse email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control-tissu" placeholder="votre@email.ma" required>
          </div>

          {{-- Téléphone --}}
          <div class="mb-3">
            <label class="form-label-tissu">Téléphone</label>
            <input type="tel" name="telephone" value="{{ old('telephone') }}"
                   class="form-control-tissu" placeholder="0661-XXXXXX">
          </div>

          {{-- Ville --}}
          <div class="mb-3">
            <label class="form-label-tissu">Ville</label>
            <input type="text" name="ville" value="{{ old('ville') }}"
                   class="form-control-tissu" placeholder="Casablanca, Fès, Marrakech…">
          </div>

          {{-- Spécialité artisan --}}
          <div class="mb-3" id="specialiteField" style="{{ old('role')=='artisan' ? '' : 'display:none;' }}">
            <label class="form-label-tissu">Spécialité artisanale</label>
            <input type="text" name="specialite" value="{{ old('specialite') }}"
                   class="form-control-tissu"
                   placeholder="Ex: Broderie Fassi, Tapis Berbère, Teinture naturelle…">
          </div>

          {{-- Mot de passe --}}
          <div class="mb-3">
            <label class="form-label-tissu">Mot de passe</label>
            <input type="password" name="password"
                   class="form-control-tissu" placeholder="Minimum 8 caractères" required>
          </div>

          <div class="mb-4">
            <label class="form-label-tissu">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation"
                   class="form-control-tissu" placeholder="Répétez le mot de passe" required>
          </div>

          <button type="submit" class="btn-or w-100" style="padding:13px;">
            <i class="bi bi-person-plus me-2"></i>Créer mon compte
          </button>
        </form>

        <p style="text-align:center;margin-top:20px;font-size:14px;color:var(--gris-doux);">
          Déjà inscrit ?
          <a href="{{ route('login') }}" style="color:var(--or-dark);font-weight:600;">Se connecter</a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function toggleRoleFields(role) {
    document.getElementById('specialiteField').style.display = role === 'artisan' ? '' : 'none';
    const specialiteInput = document.querySelector('[name="specialite"]');
    if (specialiteInput) specialiteInput.required = role === 'artisan';
  }

  document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      card.querySelector('input').checked = true;
      toggleRoleFields(card.querySelector('input').value);
    });
  });

  const checkedRole = document.querySelector('.role-card input:checked');
  if (checkedRole) {
    toggleRoleFields(checkedRole.value);
  }
</script>
@endpush