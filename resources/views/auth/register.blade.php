@extends('layouts.auth')
@section('title', 'Inscription — Tissu Artisanal')

@push('styles')
<style>
.auth-wrap {
    height: 100vh;
    overflow-y: auto;
    display: flex; align-items: center;
    background: linear-gradient(135deg, var(--sable) 0%, var(--sable-dark) 100%);
    padding: 20px;
}
.auth-card {
    background: white; border-radius: 20px;
    box-shadow: var(--shadow-lg); overflow: hidden;
    max-width: 700px; width: 100%; margin: 0 auto;
}
.auth-header {
    background: linear-gradient(135deg, var(--indigo), var(--or-dark));
    padding: 22px 36px; text-align: center; color: white;
}
.auth-header h1 { font-family:'Amiri',serif; font-size:28px; letter-spacing:0.3px; margin:0 0 4px; }
.auth-header p  { color:rgba(255,255,255,0.80); font-size:14px; margin:0; }
.auth-body { padding: 20px 28px 24px; }
.role-card {
    border: 2px solid var(--sable-dark); border-radius: 10px;
    padding: 10px 8px 8px; text-align: center; cursor: pointer;
    transition: all 0.22s ease; background: #fff;
}
.role-card input[type="radio"] { display: none; }
.role-card .role-icon-wrap {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 6px; font-size: 18px;
    transition: transform 0.2s ease;
}
.role-card:hover .role-icon-wrap,
.role-card.selected .role-icon-wrap { transform: scale(1.1); }
.role-card .role-name { font-size: 13px; font-weight: 700; color: var(--texte); margin-bottom: 2px; }
.role-card .role-desc { font-size: 11px; color: var(--gris-doux); }
#roleCards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

/* Per-role colours */
.role-card[data-role="client"]   { --rc: #c8913a; --rc-bg: #fdf5e6; }
.role-card[data-role="artisan"]  { --rc: #4a3367; --rc-bg: #f0ecf8; }
.role-card[data-role="apprenant"]{ --rc: #1a7a5e; --rc-bg: #e8f7f3; }
.role-card[data-role="livreur"]  { --rc: #c25e1a; --rc-bg: #fdf0e6; }

.role-card .role-icon-wrap { background: var(--rc-bg); color: var(--rc); }
.role-card:hover  { border-color: var(--rc); background: var(--rc-bg); }
.role-card.selected {
    border-color: var(--rc); background: var(--rc-bg);
    box-shadow: 0 4px 14px rgba(0,0,0,0.10);
}

@media (max-width: 600px) {
    #roleCards { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')
<div class="auth-wrap">
  <div class="container">
    <div class="auth-card">
      <div class="auth-header">
        <div style="
            width:52px;height:52px;border-radius:50%;
            background:rgba(255,255,255,0.18);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 10px;
        ">
          <i class="bi bi-person-plus" style="font-size:24px;color:#fff;"></i>
        </div>
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
          <div class="mb-3">
            <label class="form-label-tissu mb-2">Je suis…</label>
            <div id="roleCards">
              @foreach([
                ['client',   'Client',    'bi-bag-heart',   'Acheter des produits'],
                ['artisan',  'Artisan',   'bi-scissors',    'Vendre mes créations'],
                ['apprenant','Apprenant', 'bi-mortarboard', 'Suivre des formations'],
                ['livreur',  'Livreur',   'bi-truck',       'Livrer les commandes'],
              ] as [$val,$nom,$icon,$desc])
              <label class="role-card {{ old('role')==$val ? 'selected' : ($val=='client'&&!old('role') ? 'selected':'') }}"
                     data-role="{{ $val }}">
                <input type="radio" name="role" value="{{ $val }}"
                       {{ old('role',$val=='client'?'client':'') == $val ? 'checked' : '' }}>
                <span class="role-icon-wrap">
                  <i class="bi {{ $icon }}" style="font-size:20px;"></i>
                </span>
                <div class="role-name">{{ $nom }}</div>
                <div class="role-desc">{{ $desc }}</div>
              </label>
              @endforeach
            </div>
          </div>

          {{-- Nom & Prénom --}}
          <div class="row g-2 mb-2">
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

          {{-- Email & Téléphone --}}
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label-tissu">Email</label>
              <input type="email" name="email" value="{{ old('email') }}"
                     class="form-control-tissu" placeholder="votre@email.ma" required>
            </div>
            <div class="col-6">
              <label class="form-label-tissu">Téléphone</label>
              <input type="tel" name="telephone" value="{{ old('telephone') }}"
                     class="form-control-tissu" placeholder="0661-XXXXXX">
            </div>
          </div>

          {{-- Ville & Spécialité (spécialité visible uniquement pour artisan) --}}
          <div class="row g-2 mb-2">
            <div class="col">
              <label class="form-label-tissu">Ville</label>
              <input type="text" name="ville" value="{{ old('ville') }}"
                     class="form-control-tissu" placeholder="Casablanca, Fès, Marrakech…">
            </div>
            <div class="col" id="specialiteField" style="{{ old('role')=='artisan' ? '' : 'display:none;' }}">
              <label class="form-label-tissu">Spécialité artisanale</label>
              <input type="text" name="specialite" value="{{ old('specialite') }}"
                     class="form-control-tissu" placeholder="Broderie Fassi, Tapis Berbère…">
            </div>
          </div>

          {{-- Mot de passe & Confirmation --}}
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label-tissu">Mot de passe</label>
              <input type="password" name="password"
                     class="form-control-tissu" placeholder="Min. 8 caractères" required>
            </div>
            <div class="col-6">
              <label class="form-label-tissu">Confirmer</label>
              <input type="password" name="password_confirmation"
                     class="form-control-tissu" placeholder="Répétez" required>
            </div>
          </div>

          <button type="submit" class="btn-or w-100" style="padding:11px;">
            <i class="bi bi-person-plus me-2"></i>Créer mon compte
          </button>
        </form>

        <p style="text-align:center;margin-top:14px;font-size:14px;color:var(--gris-doux);">
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