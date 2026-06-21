@extends('layouts.app')
@section('title', 'Espace Apprenti — Tissu Artisanal')

@section('breadcrumb')
  <li class="breadcrumb-item active">Mon espace apprenti</li>
@endsection

@push('styles')
<style>
.apprenti-page {
    background: #F5F0E8;
    padding: 40px 0 72px;
    min-height: calc(100vh - 120px);
}
.apprenti-page::before {
    content: '';
    display: block;
    height: 4px;
    background: repeating-linear-gradient(
        90deg,
        #C9A84C 0 12px,
        #1B2A4A 12px 24px,
        #C9A84C 24px 36px,
        transparent 36px 48px
    );
    margin-bottom: 28px;
    opacity: 0.35;
}
.apprenti-header {
    background: linear-gradient(135deg, #1B2A4A 0%, #243656 70%, #3d2f1a 100%);
    color: #fff;
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.apprenti-header::after {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0.06;
    background-image:
        repeating-linear-gradient(45deg, #fff 0 1px, transparent 0 12px),
        repeating-linear-gradient(-45deg, #fff 0 1px, transparent 0 12px);
}
.apprenti-header > * { position: relative; z-index: 1; }
.apprenti-header h1 {
    font-family: 'Amiri', serif;
    font-size: clamp(26px, 4vw, 34px);
    margin: 0 0 6px;
    color: #fff;
}
.apprenti-header .sub { color: rgba(255,255,255,0.82); font-size: 15px; margin: 0; }
.apprenti-header .date { color: #C9A84C; font-size: 13px; margin-top: 10px; }
.stat-apprenti {
    background: #fff;
    border: 1px solid #E8D9BB;
    border-radius: var(--radius);
    padding: 20px 18px;
    height: 100%;
    box-shadow: var(--shadow-sm);
    border-top: 3px solid #C9A84C;
}
.stat-apprenti.indigo { border-top-color: #1B2A4A; }
.stat-apprenti.vert   { border-top-color: #2D6A4F; }
.stat-apprenti.or     { border-top-color: #C9A84C; }
.stat-apprenti .val {
    font-family: 'Amiri', serif;
    font-size: 32px;
    font-weight: 700;
    color: #1B2A4A;
    line-height: 1;
}
.stat-apprenti .lbl { font-size: 13px; color: var(--gris-doux); margin-top: 6px; }
.progress-global-wrap {
    background: #fff;
    border: 1px solid #E8D9BB;
    border-radius: var(--radius);
    padding: 24px 28px;
    margin-bottom: 28px;
}
.progress-global-bar {
    height: 14px;
    background: #E8D9BB;
    border-radius: 999px;
    overflow: hidden;
}
.progress-global-fill {
    height: 100%;
    background: linear-gradient(90deg, #C9A84C, #2D6A4F);
    border-radius: 999px;
    transition: width 0.8s ease;
}
.formation-card-app {
    background: #fff;
    border: 1px solid #E8D9BB;
    border-radius: var(--radius);
    padding: 22px;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s, box-shadow 0.2s;
}
.formation-card-app:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.formation-card-app .title {
    font-family: 'Amiri', serif;
    font-size: 18px;
    font-weight: 700;
    color: #1B2A4A;
    margin-bottom: 6px;
}
.formation-card-app .meta {
    font-size: 13px;
    color: var(--gris-doux);
    margin-bottom: 14px;
}
.formation-card-app .progress-mini {
    height: 8px;
    background: #F5F0E8;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0 6px;
}
.formation-card-app .progress-mini-fill {
    height: 100%;
    background: linear-gradient(90deg, #1B2A4A, #C9A84C);
    border-radius: 4px;
}
.activity-panel {
    background: #fff;
    border: 1px solid #E8D9BB;
    border-radius: var(--radius);
    padding: 22px;
    box-shadow: var(--shadow-sm);
}
.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #F5F0E8;
    font-size: 14px;
}
.activity-item:last-child { border-bottom: none; }
.activity-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #C9A84C;
    margin-top: 7px;
    flex-shrink: 0;
}
.activity-time { font-size: 12px; color: var(--gris-doux); margin-top: 4px; }
.section-title-app {
    font-family: 'Amiri', serif;
    font-size: 22px;
    color: #1B2A4A;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title-app::before {
    content: '';
    width: 4px;
    height: 24px;
    background: #C9A84C;
    border-radius: 2px;
}
</style>
@endpush

@section('content')
<div class="apprenti-page">
  <div class="container-xl">

    {{-- HEADER --}}
    <div class="apprenti-header">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
          <h1>Bonjour, {{ $user->prenom }} 👋</h1>
          <p class="sub">Suivez votre progression et vos formations</p>
        </div>
        @include('partials.date-theme-widget')
      </div>
    </div>

    {{-- STATS --}}
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-apprenti or">
          <div class="val">{{ $stats['total'] }}</div>
          <div class="lbl">Formations inscrites</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-apprenti vert">
          <div class="val">{{ $stats['terminees'] }}</div>
          <div class="lbl">Formations terminées</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-apprenti indigo">
          <div class="val">{{ $stats['enCours'] }}</div>
          <div class="lbl">Formations en cours</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-apprenti or">
          <div class="val">{{ $stats['certificats'] }}</div>
          <div class="lbl">Certificats obtenus</div>
        </div>
      </div>
    </div>

    {{-- PROGRESSION GLOBALE --}}
    <div class="progress-global-wrap">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="section-title-app mb-0" style="font-size:18px;">Progression globale</h2>
        <strong style="font-family:'Amiri',serif;font-size:28px;color:#C9A84C;">{{ $stats['progressionGlobale'] }}%</strong>
      </div>
      <div class="progress-global-bar">
        <div class="progress-global-fill" style="width:{{ $stats['progressionGlobale'] }}%;"></div>
      </div>
      <p class="text-muted small mt-2 mb-0">
        {{ $stats['terminees'] }} formation(s) terminée(s) sur {{ $stats['total'] }} inscription(s)
      </p>
    </div>

    <div class="row g-4">
      {{-- MES FORMATIONS --}}
      <div class="col-lg-8">
        <h2 class="section-title-app">Mes formations</h2>

        @if($formations->isEmpty())
          <div class="formation-card-app text-center py-5">
            <div style="font-size:48px;margin-bottom:12px;">🎓</div>
            <p class="text-muted mb-3">Vous n'êtes inscrit à aucune formation pour le moment.</p>
            <a href="{{ route('formations.index') }}" class="btn-or btn">Découvrir les formations</a>
          </div>
        @else
          <div class="row g-3">
            @foreach($formations as $inscription)
              @php
                $pct = min(100, max(0, (int) $inscription->progression));
                $formation = $inscription->formation;
              @endphp
              <div class="col-md-6">
                <div class="formation-card-app">
                  <div class="title">{{ $formation?->titre ?? 'Formation' }}</div>
                  <div class="meta">
                    <i class="bi bi-person me-1"></i>{{ $inscription->formateur_nom }}
                  </div>
                  <span class="badge-statut {{ $inscription->statutBadgeClass() }} mb-2">
                    {{ $inscription->statutLabel() }}
                  </span>
                  <div class="d-flex justify-content-between small text-muted">
                    <span>Progression</span>
                    <span>{{ $pct }}%</span>
                  </div>
                  <div class="progress-mini">
                    <div class="progress-mini-fill" style="width:{{ $pct }}%;"></div>
                  </div>
                  <div class="meta mb-3">
                    <i class="bi bi-clock me-1"></i>{{ $inscription->duree_heures }} h
                  </div>
                  <div class="mt-auto">
                    @if($inscription->statut === \App\Models\FormationApprenti::STATUT_TERMINEE)
                      @if($inscription->certificat_url)
                        <a href="{{ str_starts_with($inscription->certificat_url, 'http') ? $inscription->certificat_url : asset('storage/'.$inscription->certificat_url) }}"
                           target="_blank" class="btn-or btn btn-sm w-100">
                          <i class="bi bi-award me-1"></i>Voir certificat
                        </a>
                      @else
                        <a href="{{ route('formations.show', $formation?->id) }}" class="btn-outline-or btn btn-sm w-100">
                          Voir la formation
                        </a>
                      @endif
                    @elseif($inscription->statut === \App\Models\FormationApprenti::STATUT_EN_COURS)
                      <a href="{{ route('formations.ressources', $formation?->id) }}" class="btn-or btn btn-sm w-100">
                        <i class="bi bi-play-circle me-1"></i>Continuer
                      </a>
                    @else
                      <a href="{{ route('formations.show', $formation?->id) }}" class="btn-indigo btn btn-sm w-100">
                        <i class="bi bi-mortarboard me-1"></i>Commencer
                      </a>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- ACTIVITÉ RÉCENTE --}}
      <div class="col-lg-4">
        <div class="activity-panel">
          <h2 class="section-title-app" style="font-size:18px;">Activité récente</h2>
          @forelse($activites as $activite)
            <div class="activity-item">
              <div class="activity-dot"></div>
              <div>
                <div>{{ $activite['texte'] }}</div>
                <div class="activity-time">
                  {{ $activite['date']?->locale('fr')->diffForHumans() ?? '—' }}
                </div>
              </div>
            </div>
          @empty
            <p class="text-muted small mb-0">Aucune activité récente.</p>
          @endforelse
        </div>

        <div class="activity-panel mt-3">
          <h3 style="font-family:'Amiri',serif;font-size:16px;color:#1B2A4A;margin-bottom:12px;">Liens utiles</h3>
          <a href="{{ route('formations.index') }}" class="d-block py-2 text-decoration-none" style="color:#1B2A4A;">
            <i class="bi bi-mortarboard me-2" style="color:#C9A84C;"></i>Catalogue formations
          </a>
          <a href="{{ route('formations.mes-inscriptions') }}" class="d-block py-2 text-decoration-none" style="color:#1B2A4A;">
            <i class="bi bi-journal-check me-2" style="color:#C9A84C;"></i>Mes inscriptions
          </a>
          <a href="{{ route('profile') }}" class="d-block py-2 text-decoration-none" style="color:#1B2A4A;">
            <i class="bi bi-person me-2" style="color:#C9A84C;"></i>Mon profil
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
