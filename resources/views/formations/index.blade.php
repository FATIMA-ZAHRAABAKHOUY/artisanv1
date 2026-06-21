@extends('layouts.app')
@section('title', "Formations — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item active">Formations</li>
@endsection

@push('styles')
<style>
.formations-wrap { padding: 48px 0 80px; }
.formation-card {
    background: white; border-radius: var(--radius);
    border: 1px solid var(--sable-dark); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: all 0.3s ease;
    height: 100%; display: flex; flex-direction: column;
}
.formation-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
.formation-img {
    height: 200px; position: relative; overflow: hidden;
    background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
}
.formation-img img {
    width: 100%; height: 100%; object-fit: cover; display: block;
    transition: transform 0.4s ease;
}
.formation-card:hover .formation-img img { transform: scale(1.04); }
.formation-img-fallback {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 52px; color: rgba(255,255,255,0.25);
}
.places-bar { height: 5px; background: var(--sable-dark); border-radius: 3px; overflow: hidden; }
.places-fill { height: 100%; border-radius: 3px;
    background: linear-gradient(90deg, var(--vert-atlas), var(--ame-terre)); }
</style>
@endpush

@section('content')
<div class="formations-wrap">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-mortarboard"></i></div>
      <div>
        <h2>Formations Artisanales</h2>
        <p>Apprenez les techniques ancestrales du tissu marocain</p>
      </div>
    </div>

    @auth
      @if(auth()->user()->isClient())
        <div class="brand-banner" style="margin-bottom:24px;padding:18px 24px;
                    display:flex;align-items:center;justify-content:space-between;
                    flex-wrap:wrap;gap:12px;">
          <div>
            <div style="font-weight:600;font-size:15px;margin-bottom:3px;">
              🎓 Envie d'apprendre l'artisanat marocain ?
            </div>
            <div style="font-size:13px;color:rgba(255,255,255,0.85);">
              Contactez la coopérative pour créer un compte apprenti et
              accéder aux inscriptions.
            </div>
          </div>
          <a href="{{ route('support.index') }}"
             style="padding:9px 18px;background:white;color:var(--ame-terre-dark);
                    border-radius:var(--radius-sm);text-decoration:none;
                    font-size:13px;font-weight:600;white-space:nowrap;">
            Nous contacter
          </a>
        </div>
      @endif
    @endauth

    {{-- Filtres --}}
    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                padding:20px;margin-bottom:28px;">
      <form method="GET" action="{{ route('formations.index') }}"
            class="d-flex gap-3 align-items-end flex-wrap">
        <div>
          <label class="form-label-tissu">Ville</label>
          <input type="text" name="ville" value="{{ request('ville') }}"
                 class="form-control-tissu" placeholder="Fès, Rabat…" style="width:160px;">
        </div>
        <div>
          <label class="form-label-tissu">Prix max (MAD)</label>
          <input type="number" name="prix_max" value="{{ request('prix_max') }}"
                 class="form-control-tissu" placeholder="500" style="width:140px;">
        </div>
        <div>
          <label class="form-label-tissu">Type</label>
          <select name="gratuit" class="form-control-tissu" style="width:140px;">
            <option value="">Toutes</option>
            <option value="1" {{ request('gratuit')=='1' ? 'selected' : '' }}>Gratuites</option>
          </select>
        </div>
        <div>
          <label class="form-label-tissu">À venir</label>
          <select name="a_venir" class="form-control-tissu" style="width:140px;">
            <option value="">Toutes</option>
            <option value="1" {{ request('a_venir')=='1' ? 'selected' : '' }}>À venir</option>
          </select>
        </div>
        <button type="submit" class="btn-or" style="padding:10px 20px;">
          <i class="bi bi-search me-1"></i>Filtrer
        </button>
        <a href="{{ route('formations.index') }}"
           style="font-size:13px;color:var(--gris-doux);padding:10px 0;">Réinitialiser</a>
      </form>
    </div>

    @if($formations->isEmpty())
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:64px;margin-bottom:16px;">🎓</div>
        <h3 style="font-family:var(--font-serif);">Aucune formation trouvée</h3>
        <p style="color:var(--gris-doux);">Essayez d'autres critères</p>
      </div>
    @else
      <div class="row g-3">
        @foreach($formations as $f)
          @php
            $inscrits = $f->inscriptions()->where('statut_inscription','en_cours')->count();
            $pct = $f->places_max > 0 ? min(100, ($inscrits/$f->places_max)*100) : 0;
            $dispo = $f->places_max - $inscrits;
          @endphp
          <div class="col-md-6 col-lg-4">
            <div class="formation-card">
              <div class="formation-img">
                @if($f->image)
                  <img src="{{ $f->image_url }}"
                       alt="{{ $f->titre }}"
                       loading="lazy">
                @else
                  <div class="formation-img-fallback">
                    <i class="bi bi-mortarboard"></i>
                  </div>
                @endif
                @if($f->prix == 0)
                  <span style="position:absolute;top:12px;left:12px;background:var(--vert-atlas);
                               color:white;font-size:11px;padding:3px 10px;border-radius:20px;
                               font-weight:600;">Gratuit</span>
                @endif
                @if($dispo == 0)
                  <span style="position:absolute;top:12px;right:12px;background:var(--rouge-fes);
                               color:white;font-size:11px;padding:3px 10px;border-radius:20px;
                               font-weight:600;">Complet</span>
                @endif
              </div>

              <div style="padding:20px;flex:1;display:flex;flex-direction:column;">
                <div style="font-size:11px;color:var(--or-dark);text-transform:uppercase;
                            letter-spacing:1px;font-weight:600;margin-bottom:6px;">
                  {{ $f->artisan?->specialite }}
                </div>
                <div style="font-family:var(--font-serif);font-size:18px;font-weight:700;
                            margin-bottom:10px;line-height:1.3;flex:1;">
                  {{ $f->titre }}
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                  <span style="font-size:12.5px;color:var(--gris-doux);display:flex;align-items:center;gap:4px;">
                    <i class="bi bi-calendar3"></i>
                    {{ $f->date_debut?->format('d/m/Y') }}
                  </span>
                  <span style="font-size:12.5px;color:var(--gris-doux);display:flex;align-items:center;gap:4px;">
                    <i class="bi bi-geo-alt"></i>{{ Str::limit($f->lieu, 25) }}
                  </span>
                  <span style="font-size:12.5px;color:var(--gris-doux);display:flex;align-items:center;gap:4px;">
                    <i class="bi bi-person"></i>{{ $f->artisan?->user?->nom_complet }}
                  </span>
                </div>

                {{-- Jauge places --}}
                <div style="margin-bottom:6px;">
                  <div style="display:flex;justify-content:space-between;
                              font-size:12px;color:var(--gris-doux);margin-bottom:4px;">
                    <span>{{ $dispo }} place{{ $dispo>1?'s':'' }} restante{{ $dispo>1?'s':'' }}</span>
                    <span>{{ $inscrits }}/{{ $f->places_max }}</span>
                  </div>
                  <div class="places-bar">
                    <div class="places-fill" style="width:{{ $pct }}%"></div>
                  </div>
                </div>
              </div>

              <div style="padding:14px 20px;border-top:1px solid var(--sable-dark);
                          background:var(--sable);display:flex;align-items:center;
                          justify-content:space-between;">
                <div style="font-family:var(--font-serif);font-size:20px;font-weight:700;
                            color:{{ $f->prix==0 ? 'var(--vert-atlas)' : 'var(--or-dark)' }};">
                  {{ $f->prix == 0 ? 'Gratuit' : number_format($f->prix, 0).' MAD' }}
                </div>
                <a href="{{ route('formations.show', $f->id) }}"
                   class="btn-or" style="padding:8px 18px;font-size:13px;">
                  Voir la formation
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-center mt-4">
        {{ $formations->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection