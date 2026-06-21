@extends('layouts.app')
@section('title', "Ressources — ".$formation->titre." — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('formations.index') }}">Formations</a></li>
  <li class="breadcrumb-item"><a href="{{ route('formations.show', $formation->id) }}">{{ Str::limit($formation->titre, 30) }}</a></li>
  <li class="breadcrumb-item active">Ressources</li>
@endsection

@push('styles')
<style>
.ressource-card {
  display: flex; align-items: center; gap: 14px; padding: 16px;
  background: white; border-radius: var(--radius-sm);
  border: 1px solid var(--sable-dark); margin-bottom: 12px;
  transition: all 0.2s;
}
.ressource-card:hover { border-color: var(--or); box-shadow: var(--shadow-sm); }
.ressource-icon {
  width: 48px; height: 48px; border-radius: 10px;
  background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0; color: white;
}
.ressource-meta { font-size: 12px; color: var(--gris-doux); margin-top: 4px; }
</style>
@endpush

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
      <div>
        <a href="{{ route('formations.show', $formation->id) }}" style="color:var(--gris-doux);text-decoration:none;font-size:14px;">
          <i class="bi bi-arrow-left"></i> Retour à la formation
        </a>
        <h1 style="font-family:var(--font-serif);font-size:28px;margin:12px 0 6px;">
          📚 Ressources pédagogiques
        </h1>
        <p style="color:var(--gris-doux);margin:0;font-size:15px;">{{ $formation->titre }}</p>
      </div>
      <a href="{{ route('formations.mes-inscriptions') }}" class="btn-outline-or btn">
        Mes inscriptions
      </a>
    </div>

    @if(session('error'))
      <div class="alert-tissu error mb-4"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    @if($ressources->isEmpty())
      <div style="text-align:center;padding:60px 20px;background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);">
        <div style="font-size:48px;margin-bottom:12px;">📂</div>
        <p style="color:var(--gris-doux);margin:0;">Aucune ressource disponible pour cette formation.</p>
      </div>
    @else
      <div style="background:var(--sable);border-radius:var(--radius);padding:20px;border:1px solid var(--sable-dark);">
        @foreach($ressources as $res)
          <div class="ressource-card">
            <div class="ressource-icon">
              @if($res->type === 'video') 🎬
              @elseif($res->type === 'document_pdf') 📄
              @else 🖼️
              @endif
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:15px;">{{ $res->titre }}</div>
              @if($res->description)
                <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">{{ $res->description }}</div>
              @endif
              <div class="ressource-meta">
                @if($res->type === 'video' && $res->duree_secondes)
                  <span><i class="bi bi-clock me-1"></i>{{ gmdate('i:s', $res->duree_secondes) }}</span>
                @endif
                @if($res->nb_pages)
                  <span class="ms-2"><i class="bi bi-file-earmark me-1"></i>{{ $res->nb_pages }} pages</span>
                @endif
                @if($res->taille_ko)
                  <span class="ms-2">{{ number_format($res->taille_ko / 1024, 1) }} Mo</span>
                @endif
                @if($res->auteur)
                  <span class="ms-2"><i class="bi bi-person me-1"></i>{{ $res->auteur }}</span>
                @endif
              </div>
            </div>
            @if($res->url)
              <a href="{{ $res->url_complete }}" target="_blank" rel="noopener"
                 class="btn-or" style="padding:8px 16px;font-size:13px;white-space:nowrap;flex-shrink:0;">
                <i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir
              </a>
            @endif
          </div>
        @endforeach
      </div>
    @endif

  </div>
</div>
@endsection
