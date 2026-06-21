@extends('layouts.app')
@section('title', "Mes formations — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item active">Mes formations</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-mortarboard"></i></div>
        <div>
          <h2>Mes formations</h2>
          <p>Gérez les formations que vous avez créées</p>
        </div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('formations.index') }}" class="btn-outline-or"
           style="padding:9px 16px;font-size:13px;">
          <i class="bi bi-eye me-1"></i>Voir le catalogue public
        </a>
        <a href="{{ route('artisan.formations.create') }}" class="btn-or" style="padding:10px 20px;">
          <i class="bi bi-plus-circle me-2"></i>Créer une formation
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="row g-3">
      @forelse($formations as $f)
        <div class="col-md-6 col-lg-4">
          <div class="card-tissu p-3 h-100" style="display:flex;flex-direction:column;">
            <div style="font-weight:600;font-size:16px;margin-bottom:8px;">{{ $f->titre }}</div>
            <div style="font-size:13px;color:var(--gris-doux);margin-bottom:12px;">
              <i class="bi bi-calendar3 me-1"></i>
              {{ $f->date_debut?->format('d/m/Y') }} — {{ $f->date_fin?->format('d/m/Y') }}
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
              <span style="font-size:12px;background:var(--sable);padding:3px 10px;border-radius:12px;">
                {{ $f->inscriptions_count }} inscrit(s)
              </span>
              <span style="font-size:12px;background:var(--sable);padding:3px 10px;border-radius:12px;">
                {{ $f->en_cours ?? 0 }} en cours
              </span>
              <span class="badge-statut {{ $f->is_active ? 'badge-actif' : 'badge-inactif' }}">
                {{ $f->is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div style="margin-top:auto;display:flex;flex-wrap:wrap;gap:8px;">
              <a href="{{ route('artisan.formations.contenu', $f->id) }}"
                 class="btn-indigo" style="padding:7px 14px;font-size:12px;color:white;">
                <i class="bi bi-list-check me-1"></i>Gérer le contenu
              </a>
              <a href="{{ route('artisan.formations.edit', $f->id) }}" class="btn-outline-or" style="padding:7px 14px;font-size:12px;">
                Modifier
              </a>
              <a href="{{ route('artisan.formations.inscrits', $f->id) }}" class="btn-outline-or" style="padding:7px 14px;font-size:12px;">
                Inscrits
              </a>
              <a href="{{ route('formations.show', $f->id) }}" class="btn-outline-or" style="padding:7px 14px;font-size:12px;" target="_blank">
                Aperçu public
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="card-tissu p-4 text-center" style="color:var(--gris-doux);">
            Vous n'avez pas encore créé de formation.
            <a href="{{ route('artisan.formations.create') }}" class="d-block mt-2">Créer ma première formation</a>
            <a href="{{ route('formations.index') }}" class="d-block mt-1">Parcourir le catalogue public</a>
          </div>
        </div>
      @endforelse
    </div>

    <div class="mt-4">{{ $formations->links() }}</div>
  </div>
</div>
@endsection
