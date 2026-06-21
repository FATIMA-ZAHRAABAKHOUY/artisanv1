@extends('layouts.admin')
@section('title', 'Utilisateur — Admin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}" class="text-muted text-decoration-none">Utilisateurs</a></li>
    <li class="breadcrumb-item active text-light">{{ $user->nom_complet }}</li>
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users') }}" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Retour</a>
</div>

<div class="row g-4">
      <div class="col-lg-4">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:28px;text-align:center;">
          <div style="width:80px;height:80px;border-radius:50%;
                      background:linear-gradient(135deg,var(--or),var(--or-dark));
                      display:flex;align-items:center;justify-content:center;
                      color:white;font-size:32px;font-weight:700;
                      margin:0 auto 16px;overflow:hidden;">
            @if($user->avatar)
              <img src="{{ asset('storage/'.$user->avatar) }}"
                   style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            @else
              {{ substr($user->prenom, 0, 1) }}
            @endif
          </div>
          <div style="font-family:'Amiri',serif;font-size:20px;font-weight:700;margin-bottom:4px;">
            {{ $user->nom_complet }}
          </div>
          <div style="font-size:13px;color:var(--gris-doux);margin-bottom:12px;">
            {{ $user->email }}
          </div>
          @php $rColors=['client'=>'badge-confirmed','artisan'=>'badge-verified',
                         'admin'=>'badge-shipped','livreur'=>'badge-processing',
                         'apprenant'=>'badge-pending']; @endphp
          <span class="badge-statut {{ $rColors[$user->role] ?? '' }}" style="margin-right:6px;">
            {{ $user->role }}
          </span>
          <span class="badge-statut badge-{{ $user->statut }}">{{ $user->statut }}</span>

          <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--sable-dark);">
            <div style="display:flex;justify-content:space-around;">
              <div style="text-align:center;">
                <div style="font-family:'Amiri',serif;font-size:24px;font-weight:700;color:var(--or-dark);">
                  {{ $user->commandes()->count() }}
                </div>
                <div style="font-size:12px;color:var(--gris-doux);">Commandes</div>
              </div>
              <div style="text-align:center;">
                <div style="font-family:'Amiri',serif;font-size:24px;font-weight:700;color:var(--indigo);">
                  {{ number_format($user->commandes()->where('statut','delivered')->sum('total_ttc'),0) }}
                </div>
                <div style="font-size:12px;color:var(--gris-doux);">MAD dépensés</div>
              </div>
            </div>
          </div>

          <div style="display:flex;gap:8px;margin-top:16px;">
            @if($user->statut === 'actif')
              <form method="POST" action="{{ route('admin.users.suspendre', $user->id) }}" style="flex:1;">
                @csrf
                <button type="submit" class="w-100"
                        style="padding:9px;font-size:13px;background:none;
                               border:1px solid var(--rouge-fes);color:var(--rouge-fes);
                               border-radius:var(--radius-sm);cursor:pointer;"
                        onclick="return confirm('Suspendre ce compte ?')">
                  Suspendre
                </button>
              </form>
            @else
              <form method="POST" action="{{ route('admin.users.activer', $user->id) }}" style="flex:1;">
                @csrf
                <button type="submit" class="w-100"
                        style="padding:9px;font-size:13px;background:none;
                               border:1px solid var(--vert-atlas);color:var(--vert-atlas);
                               border-radius:var(--radius-sm);cursor:pointer;">
                  Activer
                </button>
              </form>
            @endif
          </div>
        </div>

        {{-- Profil artisan --}}
        @if($user->artisan)
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);padding:20px;margin-top:12px;">
            <h5 style="font-size:13px;font-weight:700;margin-bottom:12px;
                       text-transform:uppercase;letter-spacing:0.5px;color:var(--gris-doux);">
              Profil Artisan
            </h5>
            <div style="font-size:14px;line-height:1.9;">
              <div><strong>Spécialité :</strong> {{ $user->artisan->specialite }}</div>
              <div><strong>Statut :</strong>
                <span class="badge-statut badge-{{ $user->artisan->statut }}" style="font-size:11px;">
                  {{ $user->artisan->statut }}
                </span>
              </div>
              <div><strong>Vérifié :</strong>
                {{ $user->artisan->is_verified ? '✅ Oui' : '⏳ En attente' }}
              </div>
              <div><strong>Note :</strong> ⭐ {{ number_format($user->artisan->note_moyenne,1) }}/5</div>
              <div><strong>Adhésion :</strong> {{ $user->artisan->date_adhesion?->format('d/m/Y') }}</div>
            </div>
            @if(!$user->artisan->is_verified)
              <form method="POST" action="{{ route('admin.artisans.valider', $user->artisan->id) }}"
                    style="margin-top:10px;">
                @csrf
                <button type="submit" class="btn-or w-100" style="padding:9px;font-size:13px;">
                  ✓ Valider l'artisan
                </button>
              </form>
            @endif
          </div>
        @endif
      </div>

      {{-- Commandes récentes --}}
      <div class="col-lg-8">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:24px;">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">
            Informations personnelles
          </h3>
          <div class="row g-3">
            @foreach([
              ['Nom complet', $user->nom_complet],
              ['Email',       $user->email],
              ['Téléphone',   $user->telephone ?? '—'],
              ['Ville',       $user->ville ?? '—'],
              ['Adresse',     $user->adresse ?? '—'],
              ['Inscrit le',  $user->created_at?->format('d/m/Y H:i')],
            ] as [$lbl, $val])
              <div class="col-6">
                <div style="font-size:12px;color:var(--gris-doux);text-transform:uppercase;
                            letter-spacing:0.5px;margin-bottom:3px;">{{ $lbl }}</div>
                <div style="font-size:14px;font-weight:500;">{{ $val }}</div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Dernières commandes --}}
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:24px;margin-top:16px;">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">
            Dernières commandes
          </h3>
          @forelse($user->commandes()->latest()->take(5)->get() as $cmd)
            @php $bMap=['pending'=>'badge-pending','confirmed'=>'badge-confirmed',
                        'processing'=>'badge-processing','shipped'=>'badge-shipped',
                        'delivered'=>'badge-delivered','cancelled'=>'badge-cancelled']; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:10px 0;border-bottom:1px solid var(--sable-dark);">
              <div>
                <a href="{{ route('admin.commandes.show', $cmd->id) }}"
                   style="color:var(--or-dark);font-weight:600;">#{{ $cmd->id }}</a>
                <span style="font-size:12px;color:var(--gris-doux);margin-left:8px;">
                  {{ $cmd->created_at?->format('d/m/Y') }}
                </span>
              </div>
              <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge-statut {{ $bMap[$cmd->statut] ?? '' }}" style="font-size:11px;">
                  {{ $cmd->statut }}
                </span>
                <strong style="color:var(--or-dark);">{{ number_format($cmd->total_ttc,0) }} MAD</strong>
              </div>
            </div>
          @empty
            <p style="color:var(--gris-doux);font-size:14px;">Aucune commande.</p>
          @endforelse
        </div>
      </div>
    </div>
@endsection