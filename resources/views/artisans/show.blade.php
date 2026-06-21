@extends('layouts.app')
@section('title', $artisan->user->nom_complet." — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisans.index') }}">Artisans</a></li>
  <li class="breadcrumb-item active">{{ $artisan->user->nom_complet }}</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">
    <div class="row g-5">

      {{-- ── PROFIL ──────────────────────────────────── --}}
      <div class="col-lg-4">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:32px;
                    text-align:center;position:sticky;top:90px;">
          {{-- Avatar --}}
          <div style="width:100px;height:100px;border-radius:50%;
                      background:linear-gradient(135deg,var(--or),var(--or-dark));
                      display:flex;align-items:center;justify-content:center;
                      color:white;font-size:42px;font-weight:700;
                      margin:0 auto 20px;overflow:hidden;border:4px solid var(--sable-dark);">
            @if($artisan->user?->avatar)
              <img src="{{ asset('storage/'.$artisan->user->avatar) }}"
                   style="width:100%;height:100%;object-fit:cover;">
            @else
              {{ substr($artisan->user->prenom, 0, 1) }}
            @endif
          </div>

          <h1 style="font-family:var(--font-serif);font-size:24px;margin-bottom:6px;">
            {{ $artisan->user->nom_complet }}
          </h1>
          <div style="font-size:14px;color:var(--or-dark);margin-bottom:10px;">
            {{ $artisan->specialite }}
          </div>

          {{-- Badge vérifié --}}
          @if($artisan->is_verified)
            <div style="display:inline-flex;align-items:center;gap:6px;
                        background:#fef3c7;border:1px solid #f59e0b;
                        border-radius:20px;padding:5px 14px;
                        font-size:12.5px;color:#92400e;margin-bottom:16px;">
              <i class="bi bi-patch-check-fill" style="color:#f59e0b;"></i>
              Artisan vérifié par la coopérative
            </div>
          @endif

          {{-- Étoiles --}}
          <div style="color:var(--or);font-size:16px;margin-bottom:16px;">
            @for($s=1;$s<=5;$s++)
              <i class="bi bi-star{{ $s <= round($artisan->note_moyenne) ? '-fill' : '' }}"></i>
            @endfor
            <span style="color:var(--gris-doux);font-size:13px;margin-left:6px;">
              {{ number_format($artisan->note_moyenne,1) }}/5
            </span>
          </div>

          {{-- Infos --}}
          <div style="text-align:left;padding-top:16px;border-top:1px solid var(--sable-dark);">
            @foreach([
              ['bi-geo-alt',     $artisan->user?->ville ?? 'Maroc'],
              ['bi-calendar3',   'Membre depuis '.$artisan->date_adhesion?->format('d/m/Y')],
              ['bi-clock',       $artisan->experience_annees.' ans d\'expérience'],
              ['bi-grid',        $artisan->produits()->where('is_active',true)->count().' produits actifs'],
            ] as [$icon, $val])
              <div style="display:flex;align-items:center;gap:10px;
                          padding:10px 0;border-bottom:1px solid var(--sable-dark);
                          font-size:14px;color:var(--gris-doux);">
                <i class="bi {{ $icon }}" style="color:var(--or);font-size:16px;width:20px;flex-shrink:0;"></i>
                {{ $val }}
              </div>
            @endforeach
          </div>

          @if($artisan->bio)
            <div style="margin-top:20px;text-align:left;font-size:14px;
                        color:var(--gris-doux);line-height:1.75;">
              {{ $artisan->bio }}
            </div>
          @endif
        </div>
      </div>

      {{-- ── PRODUITS ────────────────────────────────── --}}
      <div class="col-lg-8">
        <div style="font-family:var(--font-serif);font-size:24px;font-weight:700;margin-bottom:24px;">
          Produits de {{ $artisan->user->prenom }}
          <span style="font-size:15px;color:var(--gris-doux);font-family:'DM Sans',sans-serif;">
            ({{ $produits->total() }})
          </span>
        </div>

        @if($produits->isEmpty())
          <div style="text-align:center;padding:60px 20px;
                      background:var(--sable);border-radius:var(--radius);">
            <div style="font-size:48px;margin-bottom:12px;">🧵</div>
            <p style="color:var(--gris-doux);">Cet artisan n'a pas encore publié de produits.</p>
          </div>
        @else
          <div class="row g-3">
            @foreach($produits as $produit)
              <div class="col-6 col-md-4">
                <div style="background:white;border-radius:var(--radius);
                            border:1px solid var(--sable-dark);overflow:hidden;
                            box-shadow:var(--shadow-sm);transition:all 0.3s;height:100%;">
                  <div style="aspect-ratio:1/1;background:var(--sable);overflow:hidden;
                              display:flex;align-items:center;justify-content:center;font-size:48px;">
                    @if(!empty($produit->images[0]))
                      <img src="{{ asset('storage/'.$produit->images[0]) }}"
                           alt="{{ $produit->nom }}"
                           style="width:100%;height:100%;object-fit:cover;">
                    @else 🧵 @endif
                  </div>
                  <div style="padding:14px;">
                    <div style="font-size:11px;color:var(--or-dark);text-transform:uppercase;
                                letter-spacing:0.8px;margin-bottom:4px;">
                      {{ $produit->categorie?->nom }}
                    </div>
                    <div style="font-weight:600;font-size:14px;margin-bottom:6px;line-height:1.3;">
                      <a href="{{ route('catalogue.show', $produit->id) }}"
                         style="color:inherit;text-decoration:none;">{{ $produit->nom }}</a>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                      <div style="font-family:var(--font-serif);font-size:18px;
                                  font-weight:700;color:var(--or-dark);">
                        {{ number_format($produit->prix, 0) }}
                        <span style="font-size:12px;color:var(--gris-doux);font-family:'DM Sans',sans-serif;">MAD</span>
                      </div>
                      @auth
                        <form method="POST" action="{{ route('panier.ajouter', $produit->id) }}">
                          @csrf
                          <button type="submit"
                                  style="width:34px;height:34px;background:var(--ame-charbon);color:white;
                                         border:none;border-radius:50%;cursor:pointer;font-size:15px;">
                            <i class="bi bi-bag-plus"></i>
                          </button>
                        </form>
                      @endauth
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="d-flex justify-content-center mt-4">
            {{ $produits->links() }}
          </div>
        @endif

        {{-- Formations de l'artisan --}}
        @if($artisan->formations()->where('is_active',true)->exists())
          <div style="margin-top:48px;">
            <div style="font-family:var(--font-serif);font-size:22px;font-weight:700;margin-bottom:20px;">
              Formations proposées
            </div>
            <div class="row g-3">
              @foreach($artisan->formations()->where('is_active',true)->where('date_debut','>=',now())->take(3)->get() as $f)
                <div class="col-md-6">
                  <div style="background:linear-gradient(135deg,var(--ame-charbon-deep),var(--ame-terre-dark));
                              border-radius:var(--radius);padding:20px;color:white;">
                    <div style="font-family:var(--font-serif);font-size:16px;font-weight:700;margin-bottom:8px;">
                      {{ $f->titre }}
                    </div>
                    <div style="font-size:13px;opacity:0.8;margin-bottom:12px;">
                      <i class="bi bi-calendar3 me-1"></i>{{ $f->date_debut?->format('d/m/Y') }}
                      · <i class="bi bi-geo-alt me-1"></i>{{ $f->lieu }}
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                      <div style="font-family:var(--font-serif);font-size:20px;font-weight:700;">
                        {{ $f->prix == 0 ? 'Gratuit' : number_format($f->prix,0).' MAD' }}
                      </div>
                      <a href="{{ route('formations.show', $f->id) }}"
                         style="padding:7px 16px;background:rgba(255,255,255,0.2);
                                color:white;border-radius:var(--radius-sm);
                                text-decoration:none;font-size:13px;font-weight:500;">
                        Voir →
                      </a>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection