@extends('layouts.app')
@section('title', 'Fournisseurs — Tissu Artisanal')

@section('breadcrumb')
  <li class="breadcrumb-item active">Fournisseurs</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-building"></i></div>
        <div>
          <h2>Nos Fournisseurs Partenaires</h2>
          <p>Matériaux et outils pour vos formations artisanales</p>
        </div>
      </div>
      <form method="GET" action="{{ route('fournisseurs.index') }}" style="display:flex;gap:8px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-tissu"
               placeholder="Rechercher…" style="min-width:180px;">
        <button type="submit" class="btn-or btn btn-sm"><i class="bi bi-search"></i></button>
      </form>
    </div>

    <div style="display:flex;gap:4px;margin-bottom:28px;border-bottom:2px solid var(--sable-dark);flex-wrap:wrap;">
      @foreach([''=>'Tous','local'=>'🏪 Locaux','national'=>'🚚 Nationaux','en_ligne'=>'🌐 En ligne'] as $v=>$l)
        <a href="{{ route('fournisseurs.index', array_filter(['type'=>$v ?: null, 'q'=>request('q')])) }}"
           style="padding:10px 18px;font-size:13.5px;font-weight:500;text-decoration:none;
                  border-bottom:2px solid {{ request('type','')===$v ? 'var(--or)':'transparent' }};
                  margin-bottom:-2px;
                  color:{{ request('type','')===$v ? 'var(--or-dark)':'var(--gris-doux)' }};">
          {{ $l }}
        </a>
      @endforeach
    </div>

    <div class="row g-3">
      @forelse($fournisseurs as $f)
        <div class="col-md-6 col-lg-4">
          <div class="card-tissu" style="padding:24px;height:100%;">

            <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:14px;">
              @if($f->getLogoUrl())
                <img src="{{ $f->getLogoUrl() }}" alt="" style="width:52px;height:52px;border-radius:12px;object-fit:cover;flex-shrink:0;">
              @else
                <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,var(--or),var(--or-dark));
                            display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:white;flex-shrink:0;">
                  {{ strtoupper(substr($f->nom, 0, 1)) }}
                </div>
              @endif
              <div style="flex:1;min-width:0;">
                <div style="font-family:'Amiri',serif;font-size:17px;font-weight:700;">{{ $f->nom }}</div>
                <div style="font-size:13px;color:var(--or-dark);margin-top:2px;">{{ $f->getTypeLabel() }}@if($f->ville) — {{ $f->ville }}@endif</div>
              </div>
            </div>

            @if($f->note_moyenne)
              <div style="font-size:13px;margin-bottom:10px;color:var(--or-dark);">
                @for($i=1;$i<=5;$i++)
                  <i class="bi bi-star{{ $i <= round($f->note_moyenne) ? '-fill' : '' }}"></i>
                @endfor
                <span style="color:var(--gris-doux);margin-left:4px;">({{ number_format($f->note_moyenne, 1) }})</span>
              </div>
            @endif

            @if($f->remise_cooperative > 0)
              <div style="background:var(--sable);border-radius:var(--radius-sm);padding:8px 12px;font-size:12.5px;
                          color:var(--vert-atlas);font-weight:600;margin-bottom:14px;">
                🎁 {{ $f->remise_cooperative }}% remise coopérative
              </div>
            @endif

            <a href="{{ route('fournisseurs.show', $f->id) }}" class="btn-indigo btn btn-sm w-100">
              Voir détails <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      @empty
        <div class="col-12" style="text-align:center;padding:80px;color:var(--gris-doux);">
          <div style="font-size:64px;margin-bottom:16px;">🏭</div>
          <h3 style="font-family:'Amiri',serif;">Aucun fournisseur disponible</h3>
        </div>
      @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
      {{ $fournisseurs->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
