@extends('layouts.app')
@section('title', "Mes formations — L'Âme du Fil")
@section('breadcrumb')
  <li class="breadcrumb-item active">Mes formations</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-mortarboard"></i></div>
      <div>
        <h2>Mes Formations</h2>
        <p>Suivez votre progression dans les formations artisanales</p>
      </div>
    </div>

    {{-- Onglets statut --}}
    <div style="display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid var(--sable-dark);">
      @foreach(['tous'=>'Toutes','en_cours'=>'En cours','terminee'=>'Terminées','abandonnee'=>'Abandonnées'] as $val=>$lbl)
        <a href="{{ route('formations.mes-inscriptions', $val!='tous' ? ['statut'=>$val] : []) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:500;text-decoration:none;
                  border-bottom:2px solid {{ (request('statut')==$val||((!request('statut'))&&$val=='tous')) ? 'var(--or)':'transparent' }};
                  margin-bottom:-2px;
                  color:{{ (request('statut')==$val||((!request('statut'))&&$val=='tous')) ? 'var(--or-dark)':'var(--gris-doux)' }};">
          {{ $lbl }}
        </a>
      @endforeach
    </div>

    @forelse($inscriptions as $ins)
      @php
        $statusColors = [
          'en_cours'   => ['border'=>'var(--ame-terre)',         'bg'=>'rgba(155,74,58,0.04)'],
          'terminee'   => ['border'=>'var(--vert-atlas)', 'bg'=>'rgba(45,106,79,0.04)'],
          'abandonnee' => ['border'=>'var(--rouge-fes)',  'bg'=>'rgba(160,48,42,0.04)'],
          'suspendue'  => ['border'=>'var(--gris-doux)',  'bg'=>'var(--sable)'],
        ];
        $sc = $statusColors[$ins->statut_inscription] ?? $statusColors['en_cours'];
      @endphp
      <div style="background:{{ $sc['bg'] }};border:1.5px solid {{ $sc['border'] }};
                  border-radius:var(--radius);padding:24px;margin-bottom:16px;">
        <div class="row g-3 align-items-center">
          <div class="col-lg-7">
            {{-- Titre --}}
            <div style="font-family:var(--font-serif);font-size:20px;font-weight:700;margin-bottom:6px;">
              {{ $ins->formation?->titre }}
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:13px;
                        color:var(--gris-doux);margin-bottom:14px;">
              <span><i class="bi bi-person me-1"></i>{{ $ins->formation?->artisan?->user?->nom_complet }}</span>
              <span><i class="bi bi-geo-alt me-1"></i>{{ $ins->formation?->lieu }}</span>
              <span><i class="bi bi-calendar3 me-1"></i>
                {{ $ins->formation?->date_debut?->format('d/m/Y') }}
                → {{ $ins->formation?->date_fin?->format('d/m/Y') }}
              </span>
            </div>

            {{-- Statut badge --}}
            @php
              $sBadge = ['en_cours'=>['badge-processing','📚 En cours'],
                         'terminee'=>['badge-delivered','🎓 Terminée'],
                         'abandonnee'=>['badge-cancelled','🚪 Abandonnée'],
                         'suspendue'=>['badge-pending','⏸️ Suspendue']];
              [$cls,$lbl] = $sBadge[$ins->statut_inscription] ?? ['badge-pending',$ins->statut_inscription];
            @endphp
            <span class="badge-statut {{ $cls }}" style="font-size:12px;">{{ $lbl }}</span>

            {{-- Dates inscription --}}
            <div style="font-size:12.5px;color:var(--gris-doux);margin-top:8px;">
              Inscrit le {{ $ins->date_inscription?->format('d/m/Y') }}
              @if($ins->date_fin_reelle)
                · Terminé le {{ $ins->date_fin_reelle->format('d/m/Y') }}
              @endif
            </div>
          </div>

          <div class="col-lg-5">
            {{-- Progression --}}
            @if($ins->statut_inscription === 'en_cours')
              <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;
                            font-size:13px;font-weight:600;margin-bottom:6px;">
                  <span>Progression</span>
                  <span style="color:var(--or-dark);">{{ $ins->progression }}%</span>
                </div>
                <div style="height:8px;background:var(--sable-dark);border-radius:4px;overflow:hidden;">
                  <div style="height:100%;width:{{ $ins->progression }}%;
                              background:linear-gradient(90deg,var(--or),var(--or-dark));
                              border-radius:4px;transition:width 0.5s;"></div>
                </div>
              </div>
            @endif

            {{-- Note finale --}}
            @if($ins->note_finale)
              <div style="text-align:center;margin-bottom:14px;">
                <div style="font-family:var(--font-serif);font-size:32px;font-weight:700;
                            color:var(--vert-atlas);">{{ $ins->note_finale }}/20</div>
                <div style="font-size:12px;color:var(--gris-doux);">Note finale</div>
              </div>
            @endif

            {{-- Actions --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <a href="{{ route('formations.show', $ins->formation_id) }}"
                 class="btn-indigo" style="padding:8px 16px;font-size:13px;
                 display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:white;">
                <i class="bi bi-eye"></i>Voir la formation
              </a>

              @if($ins->certificat_url)
                <a href="{{ $ins->certificat_url }}" target="_blank"
                   class="btn-or" style="padding:8px 16px;font-size:13px;
                   display:inline-flex;align-items:center;gap:6px;">
                  <i class="bi bi-award"></i>Certificat
                </a>
              @endif

              @if($ins->statut_inscription === 'en_cours')
                <form method="POST" action="{{ route('formations.abandonner', $ins->id) }}">
                  @csrf
                  <button type="submit"
                          style="padding:8px 16px;font-size:13px;background:none;
                                 border:1px solid var(--rouge-fes);color:var(--rouge-fes);
                                 border-radius:var(--radius-sm);cursor:pointer;"
                          onclick="return confirm('Abandonner cette formation ?')">
                    Abandonner
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:72px;margin-bottom:16px;">🎓</div>
        <h3 style="font-family:var(--font-serif);">Aucune formation</h3>
        <p style="color:var(--gris-doux);margin-bottom:28px;">
          Vous n'êtes inscrit à aucune formation pour le moment.
        </p>
        <a href="{{ route('formations.index') }}" class="btn-or">
          <i class="bi bi-mortarboard me-2"></i>Découvrir les formations
        </a>
      </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
      {{ $inscriptions->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection