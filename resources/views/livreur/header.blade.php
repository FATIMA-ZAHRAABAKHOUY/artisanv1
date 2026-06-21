@auth
<div style="background:var(--indigo);padding:14px 20px;display:flex;
            align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:10px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <div style="width:36px;height:36px;border-radius:50%;
                background:var(--or);color:white;display:flex;
                align-items:center;justify-content:center;
                font-weight:700;font-size:14px;flex-shrink:0;overflow:hidden;">
      @if(auth()->user()->avatar)
        <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
      @else
        {{ strtoupper(substr(auth()->user()->prenom ?? 'L', 0, 1)) }}
      @endif
    </div>
    <div>
      <div style="color:white;font-size:14px;font-weight:600;">
        {{ auth()->user()->nom_complet }}
      </div>
      <div style="color:rgba(255,255,255,0.6);font-size:11px;">
        Livreur
      </div>
    </div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="{{ route('livreur.profil') }}"
       style="padding:7px 14px;font-size:13px;color:white;
              background:rgba(255,255,255,0.12);border-radius:var(--radius-sm);
              text-decoration:none;display:flex;align-items:center;gap:6px;">
      <i class="bi bi-person-circle"></i> Mon profil
    </a>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit"
              style="padding:7px 14px;font-size:13px;color:white;
                     background:rgba(160,48,42,0.3);border:none;
                     border-radius:var(--radius-sm);cursor:pointer;
                     display:flex;align-items:center;gap:6px;">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </button>
    </form>
  </div>
</div>
@endauth
