@extends('layouts.app')
@section('title', $formation->titre." — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('formations.index') }}">Formations</a></li>
  <li class="breadcrumb-item active">{{ Str::limit($formation->titre, 40) }}</li>
@endsection

@push('styles')
<style>
.etape-card {
    display:flex; gap:16px; padding:18px 0;
    border-bottom:1px solid var(--sable-dark);
}
.etape-num {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,var(--ame-terre),var(--ame-terre-dark));
    color:white; display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:14px; flex-shrink:0;
}
.materiau-card, .outil-card {
    background:white; border-radius:var(--radius-sm);
    border:1px solid var(--sable-dark); padding:16px;
    margin-bottom:10px;
}
.fournisseur-chip {
    display:inline-flex; align-items:center; gap:6px;
    background:var(--sable); border:1px solid var(--sable-dark);
    border-radius:20px; padding:4px 12px; font-size:12px;
    color:var(--texte); text-decoration:none; margin:3px;
    transition:all 0.2s;
}
.fournisseur-chip:hover { border-color:var(--or); color:var(--or-dark); }
.ressource-card {
    display:flex; align-items:center; gap:14px; padding:14px;
    background:white; border-radius:var(--radius-sm);
    border:1px solid var(--sable-dark); margin-bottom:10px;
    transition:all 0.2s;
}
.ressource-card:hover { border-color:var(--or); box-shadow:var(--shadow-sm); }
</style>
@endpush

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">
    <div class="row g-5">

      {{-- ── GAUCHE : Contenu ────────────────────────────── --}}
      <div class="col-lg-8">

        {{-- Header --}}
        <div class="brand-banner" style="margin-bottom:28px;">
          <div>
            <div style="font-size:11px;color:rgba(255,255,255,0.65);text-transform:uppercase;
                        letter-spacing:1.5px;margin-bottom:10px;">
              {{ $formation->artisan?->specialite }}
            </div>
            <h1 style="font-family:var(--font-serif);font-size:32px;color:white;margin-bottom:16px;line-height:1.2;">
              {{ $formation->titre }}
            </h1>
            <div style="display:flex;flex-wrap:wrap;gap:16px;">
              <span style="color:rgba(255,255,255,0.85);font-size:14px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-calendar3"></i>
                Du {{ $formation->date_debut?->format('d/m/Y') }}
                au {{ $formation->date_fin?->format('d/m/Y') }}
              </span>
              <span style="color:rgba(255,255,255,0.85);font-size:14px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-geo-alt"></i>{{ $formation->lieu }}
              </span>
              <span style="color:rgba(255,255,255,0.85);font-size:14px;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-people"></i>{{ $formation->places_max }} places max
              </span>
            </div>
          </div>
        </div>

        {{-- Tabs --}}
        <div class="tab-nav">
          <a href="#description" class="active" data-tab="description">📋 Description</a>
          <a href="#programme" data-tab="programme">📚 Programme ({{ $formation->etapes->count() }} étapes)</a>
          <a href="#materiaux" data-tab="materiaux">🧵 Matériaux ({{ $formation->materiaux->count() }})</a>
          <a href="#outils" data-tab="outils">🔧 Outils ({{ $formation->outils->count() }})</a>
          <a href="#ressources" data-tab="ressources">📹 Ressources ({{ $ressourcesAffichees->count() }})</a>
          @if($estInscrit)
            <a href="#mon-espace" data-tab="mon-espace" style="color:var(--vert-atlas);">🎓 Mon espace</a>
          @endif
        </div>

        {{-- Tab : Description --}}
        <div id="tab-description">
          <p style="font-size:15px;line-height:1.85;color:var(--gris-doux);margin-bottom:24px;">
            {{ $formation->description }}
          </p>

          {{-- Formateurs --}}
          @if($formation->formateurs->count() > 0)
            <h3 style="font-family:var(--font-serif);font-size:20px;margin-bottom:16px;">
              Formateurs
            </h3>
            @foreach($formation->formateurs as $fm)
              <div style="display:flex;align-items:center;gap:14px;padding:14px;
                          background:var(--sable);border-radius:var(--radius-sm);margin-bottom:10px;">
                <div style="width:50px;height:50px;border-radius:50%;
                            background:linear-gradient(135deg,var(--ame-terre),var(--ame-terre-dark));
                            display:flex;align-items:center;justify-content:center;
                            color:white;font-size:20px;flex-shrink:0;">
                  {{ substr($fm->user?->prenom ?? 'F', 0, 1) }}
                </div>
                <div>
                  <div style="font-weight:600;font-size:15px;">{{ $fm->user?->nom_complet }}</div>
                  <div style="font-size:13px;color:var(--or-dark);">
                    {{ $fm->pivot->role === 'principal' ? '👨‍🏫 Formateur principal' : ($fm->pivot->role === 'assistant' ? '🤝 Assistant' : '🎤 Intervenant') }}
                    @if($fm->est_externe) — {{ $fm->organisme }} @endif
                  </div>
                  @if($fm->specialite)
                    <div style="font-size:12.5px;color:var(--gris-doux);">{{ $fm->specialite }}</div>
                  @endif
                </div>
              </div>
            @endforeach
          @endif
        </div>

        {{-- Tab : Programme --}}
        <div id="tab-programme" style="display:none;">
          @forelse($formation->etapes as $etape)
            <div class="etape-card">
              <div class="etape-num">{{ $etape->numero_ordre }}</div>
              <div style="flex:1;">
                <div style="font-weight:600;font-size:15px;margin-bottom:6px;">{{ $etape->titre }}</div>
                @if($etape->description)
                  <p style="font-size:14px;color:var(--gris-doux);margin-bottom:8px;line-height:1.7;">
                    {{ $etape->description }}
                  </p>
                @endif
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                  @if($etape->duree_minutes)
                    <span style="font-size:12px;background:var(--sable);
                                 border-radius:20px;padding:3px 10px;">
                      <i class="bi bi-clock me-1"></i>{{ $etape->duree_minutes }} min
                    </span>
                  @endif
                  @if($etape->objectif)
                    <span style="font-size:12px;color:var(--vert-atlas);">
                      <i class="bi bi-bullseye me-1"></i>{{ $etape->objectif }}
                    </span>
                  @endif
                </div>
              </div>
            </div>
          @empty
            <p style="color:var(--gris-doux);">Programme en cours de préparation.</p>
          @endforelse
        </div>

        {{-- Tab : Matériaux --}}
        <div id="tab-materiaux" style="display:none;">
          <div class="alert-tissu info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            Les matériaux marqués <strong>À apporter</strong> sont à préparer avant la formation.
            Des suggestions de fournisseurs sont indiquées pour vous aider.
          </div>
          @auth
            @if(auth()->user()->isApprenant())
              <a href="{{ route('formations.fournisseurs', $formation->id) }}"
                 class="btn-indigo" style="margin-bottom:16px;display:inline-flex;align-items:center;">
                <i class="bi bi-shop me-2"></i>Où acheter le matériel ?
              </a>
            @endif
          @endauth
          @forelse($formation->materiaux as $mat)
            <div class="materiau-card">
              <div style="display:flex;align-items:center;justify-content:space-between;
                          margin-bottom:8px;flex-wrap:wrap;gap:8px;">
                <div style="font-weight:600;font-size:15px;">{{ $mat->nom }}</div>
                <span style="font-size:12px;padding:3px 10px;border-radius:20px;
                             background:{{ $mat->est_fourni ? '#d1fae5' : '#fef3c7' }};
                             color:{{ $mat->est_fourni ? '#065f46' : '#92400e' }};">
                  {{ $mat->est_fourni ? '✅ Fourni par l\'artisan' : '🎒 À apporter' }}
                </span>
              </div>
              <div style="font-size:13px;color:var(--gris-doux);margin-bottom:8px;">
                {{ $mat->description }}
                @if($mat->quantite)
                  — <strong>{{ $mat->quantite }} {{ $mat->unite }}</strong>
                @endif
                @if($mat->couleur)
                  — Couleur : <strong>{{ $mat->couleur }}</strong>
                @endif
              </div>
              @if($mat->fournisseurs->filter(fn($fm) => $fm->fournisseur && $fm->fournisseur->statut === 'actif')->count() > 0)
                <div>
                  <div style="font-size:12px;color:var(--gris-doux);margin-bottom:6px;">
                    Où acheter :
                  </div>
                  @foreach($mat->fournisseurs as $fm)
                    @continue(!$fm->fournisseur || $fm->fournisseur->statut !== 'actif')
                    <a href="#" class="fournisseur-chip">
                      @if($fm->fournisseur->type === 'local') 🏪
                      @elseif($fm->fournisseur->type === 'national') 🚚
                      @else 🌐 @endif
                      {{ $fm->fournisseur->nom }}
                      @if($fm->prix_unitaire)
                        — {{ number_format($fm->prix_unitaire, 2) }} MAD / {{ $fm->unite_prix }}
                      @endif
                      @if($fm->est_recommande)
                        <span style="color:var(--vert-atlas);">★</span>
                      @endif
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          @empty
            <p style="color:var(--gris-doux);">Aucun matériau spécifié.</p>
          @endforelse
        </div>

        {{-- Tab : Outils --}}
        <div id="tab-outils" style="display:none;">
          @forelse($formation->outils as $outil)
            <div class="outil-card">
              <div style="display:flex;align-items:center;justify-content:space-between;
                          margin-bottom:6px;flex-wrap:wrap;gap:8px;">
                <div style="font-weight:600;font-size:15px;">{{ $outil->nom }}</div>
                <span style="font-size:12px;padding:3px 10px;border-radius:20px;
                             background:{{ $outil->est_fourni ? '#d1fae5' : '#fef3c7' }};
                             color:{{ $outil->est_fourni ? '#065f46' : '#92400e' }};">
                  {{ $outil->est_fourni ? '✅ Fourni' : '🎒 À apporter (× '.$outil->quantite.')' }}
                </span>
              </div>
              @if($outil->description)
                <p style="font-size:13.5px;color:var(--gris-doux);margin-bottom:8px;">{{ $outil->description }}</p>
              @endif
              @if($outil->fournisseurs->filter(fn($fo) => $fo->fournisseur && $fo->fournisseur->statut === 'actif')->count() > 0)
                <div>
                  <div style="font-size:12px;color:var(--gris-doux);margin-bottom:5px;">Où acheter :</div>
                  @foreach($outil->fournisseurs as $fo)
                    @continue(!$fo->fournisseur || $fo->fournisseur->statut !== 'actif')
                    <a href="{{ $fo->url_produit ?? '#' }}" class="fournisseur-chip"
                       {{ $fo->url_produit ? 'target=_blank' : '' }}>
                      {{ $fo->fournisseur->nom }}
                      @if($fo->prix_unitaire) — {{ number_format($fo->prix_unitaire,2) }} MAD @endif
                      @if($fo->est_recommande)<span style="color:var(--vert-atlas);">★</span>@endif
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          @empty
            <p style="color:var(--gris-doux);">Aucun outil spécifié.</p>
          @endforelse
        </div>

        {{-- Tab : Ressources --}}
        <div id="tab-ressources" style="display:none;">
          @if(!$peutVoirToutesRessources && $formation->ressources->where('est_public', false)->count() > 0)
            <div class="alert-tissu info mb-4">
              <i class="bi bi-lock me-2"></i>
              Certaines ressources sont réservées aux apprenants inscrits.
              @guest
                <a href="{{ route('login') }}">Connectez-vous</a> et inscrivez-vous pour y accéder.
              @endguest
            </div>
          @endif
          @forelse($ressourcesAffichees as $res)
            <div class="card-tissu p-3 mb-3">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                <span style="font-size:18px;">
                  @if($res->type==='video') 🎬
                  @elseif($res->type==='document_pdf') 📄
                  @elseif($res->type==='image') 🖼️
                  @else 🔗 @endif
                </span>
                <strong>{{ $res->titre }}</strong>
                @if(!$res->est_public)
                  <span style="font-size:11px;background:#fef3c7;color:#92400e;border-radius:10px;padding:2px 8px;">
                    Réservé aux inscrits
                  </span>
                @endif
              </div>

              @if($res->description)
                <p style="font-size:13px;color:var(--gris-doux);margin-bottom:10px;">{{ $res->description }}</p>
              @endif

              @if($res->type === 'video' && $res->isUploadedFile())
                <video controls style="width:100%;max-width:600px;border-radius:8px;">
                  <source src="{{ $res->url_complete }}" type="video/mp4">
                  Votre navigateur ne supporte pas la lecture vidéo.
                </video>
              @elseif($res->type === 'video')
                <a href="{{ $res->url_complete }}" target="_blank" class="btn-indigo"
                   style="display:inline-flex;align-items:center;gap:8px;color:white;">
                  <i class="bi bi-play-circle"></i> Regarder la vidéo
                </a>
              @elseif($res->type === 'document_pdf')
                <a href="{{ $res->url_complete }}" target="_blank" class="btn-or"
                   style="display:inline-flex;align-items:center;gap:8px;">
                  <i class="bi bi-file-earmark-pdf"></i>
                  Télécharger le PDF @if($res->nb_pages) ({{ $res->nb_pages }} pages) @endif
                </a>
              @elseif($res->type === 'image')
                <a href="{{ $res->url_complete }}" target="_blank" title="Ouvrir l'image en grand">
                  <img src="{{ $res->url_complete }}" alt="{{ $res->titre }}"
                       loading="lazy"
                       style="max-width:100%;max-height:360px;width:100%;object-fit:cover;
                              border-radius:8px;border:1px solid var(--sable-dark);">
                </a>
              @else
                <a href="{{ $res->url_complete }}" target="_blank" class="btn-outline-or">
                  Voir la ressource →
                </a>
              @endif
            </div>
          @empty
            <p style="color:var(--gris-doux);">Aucune ressource disponible pour cette formation.</p>
          @endforelse
        </div>

        {{-- Tab : Mon Espace (si inscrit) --}}
        @if($estInscrit && $inscription)
          <div id="tab-mon-espace" style="display:none;">
            <div style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);
                        border-radius:var(--radius);padding:24px;margin-bottom:20px;
                        border:1px solid #6ee7b7;">
              <div style="font-family:var(--font-serif);font-size:20px;font-weight:700;
                          margin-bottom:8px;color:#065f46;">
                🎓 Mon inscription
              </div>
              <div style="font-size:14px;color:#065f46;margin-bottom:16px;">
                Statut : <strong>{{ $inscription->statut_inscription }}</strong>
              </div>
              <div style="margin-bottom:8px;font-size:13px;color:#065f46;">
                Progression : {{ $inscription->progression }}%
              </div>
              <div style="height:10px;background:rgba(0,0,0,0.1);border-radius:5px;overflow:hidden;">
                <div style="height:100%;width:{{ $inscription->progression }}%;
                            background:var(--vert-atlas);border-radius:5px;
                            transition:width 0.5s;"></div>
              </div>
              @if($inscription->note_finale)
                <div style="margin-top:12px;font-size:15px;font-weight:600;color:#065f46;">
                  Note finale : {{ $inscription->note_finale }}/20
                </div>
              @endif
              @if($inscription->certificat_url)
                <a href="{{ $inscription->certificat_url }}" target="_blank"
                   class="btn-indigo" style="margin-top:14px;display:inline-flex;align-items:center;gap:8px;">
                  <i class="bi bi-award"></i>Télécharger mon certificat
                </a>
              @endif
            </div>
          </div>
        @endif

      </div>

      {{-- ── DROITE : Inscription ────────────────────────── --}}
      <div class="col-lg-4">
        <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                    padding:28px;position:sticky;top:90px;">

          {{-- Prix --}}
          <div style="text-align:center;margin-bottom:20px;
                      padding-bottom:20px;border-bottom:1px solid var(--sable-dark);">
            <div style="font-family:var(--font-serif);font-size:42px;font-weight:700;
                        color:{{ $formation->prix==0 ? 'var(--vert-atlas)' : 'var(--or-dark)' }};">
              {{ $formation->prix == 0 ? 'Gratuit' : number_format($formation->prix,0).' MAD' }}
            </div>
            <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">Par personne</div>
          </div>

          {{-- Infos clés --}}
          @foreach([
            ['bi-calendar3',  'Dates', $formation->date_debut?->format('d/m/Y').' → '.$formation->date_fin?->format('d/m/Y')],
            ['bi-geo-alt',    'Lieu',  $formation->lieu],
            ['bi-people',     'Places',($formation->places_max - $inscrits).' / '.$formation->places_max.' disponibles'],
            ['bi-clock',      'Durée', ($formation->date_debut && $formation->date_fin) ? $formation->date_debut->diffInDays($formation->date_fin)+1 .' jour(s)' : 'À définir'],
          ] as [$icon, $label, $val])
            <div style="display:flex;gap:12px;align-items:flex-start;
                        margin-bottom:14px;">
              <i class="bi {{ $icon }}" style="color:var(--or);font-size:16px;margin-top:2px;"></i>
              <div>
                <div style="font-size:11.5px;color:var(--gris-doux);text-transform:uppercase;
                            letter-spacing:0.5px;margin-bottom:2px;">{{ $label }}</div>
                <div style="font-size:14px;font-weight:500;">{{ $val }}</div>
              </div>
            </div>
          @endforeach

          {{-- Jauge places --}}
          @php
            $inscrits = $formation->inscriptions()->where('statut_inscription','en_cours')->count();
            $pct = $formation->places_max > 0 ? min(100,($inscrits/$formation->places_max)*100) : 0;
          @endphp
          <div style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;
                        font-size:12px;color:var(--gris-doux);margin-bottom:5px;">
              <span>Places occupées</span>
              <span>{{ round($pct) }}%</span>
            </div>
            <div class="places-bar"><div class="places-fill" style="width:{{ $pct }}%"></div></div>
          </div>

          {{-- Bouton inscription --}}
          @if($estInscrit)
            <div style="background:#d1fae5;border-radius:var(--radius-sm);
                        padding:14px;text-align:center;margin-bottom:12px;">
              <div style="font-weight:600;color:#065f46;font-size:15px;">
                ✅ Vous êtes inscrit(e)
              </div>
              <div style="font-size:13px;color:#059669;margin-top:4px;">
                Progression : {{ $inscription?->progression ?? 0 }}%
              </div>
            </div>
            <form method="POST" action="{{ route('formations.abandonner', $inscription->id) }}">
              @csrf
              <button type="submit"
                      style="width:100%;padding:11px;font-size:13px;background:none;
                             border:1.5px solid var(--rouge-fes);color:var(--rouge-fes);
                             border-radius:var(--radius-sm);cursor:pointer;"
                      onclick="return confirm('Abandonner cette formation ?')">
                Abandonner la formation
              </button>
            </form>
          @elseif($formation->estComplete())
            <button class="btn-or w-100" disabled
                    style="padding:14px;font-size:15px;opacity:0.5;cursor:not-allowed;">
              <i class="bi bi-x-circle me-2"></i>Formation complète
            </button>
          @else
            @auth
              @if(auth()->user()->isApprenant())
                <form method="POST" action="{{ route('formations.inscrire', $formation->id) }}">
                  @csrf
                  <button type="submit" class="btn-or w-100"
                          style="padding:14px;font-size:15px;"
                          {{ $formation->estComplete() ? 'disabled' : '' }}>
                    <i class="bi bi-mortarboard me-2"></i>
                    {{ $formation->estComplete() ? 'Formation complète' : "S'inscrire maintenant" }}
                  </button>
                </form>
              @elseif(auth()->user()->isClient())
                <div style="background:var(--sable);border-radius:var(--radius);
                            padding:16px 20px;display:flex;align-items:center;gap:12px;">
                  <i class="bi bi-info-circle" style="font-size:22px;color:var(--ame-charbon);"></i>
                  <div>
                    <div style="font-weight:600;font-size:14px;color:var(--texte);">
                      Formation réservée aux apprentis
                    </div>
                    <div style="font-size:13px;color:var(--gris-doux);margin-top:2px;">
                      Pour vous inscrire à cette formation, créez un compte apprenti
                      ou contactez la coopérative.
                    </div>
                  </div>
                </div>
              @elseif(auth()->user()->isAdmin() || auth()->user()->isArtisan())
                <div style="background:var(--sable);border-radius:var(--radius);
                            padding:14px 18px;font-size:13px;color:var(--gris-doux);">
                  <i class="bi bi-eye me-2"></i>Vue {{ auth()->user()->isAdmin() ? 'administrateur' : 'artisan' }} — inscription non disponible pour ce rôle
                </div>
              @else
                <div style="background:var(--sable);border-radius:var(--radius);
                            padding:14px 18px;font-size:13px;color:var(--gris-doux);">
                  <i class="bi bi-info-circle me-2"></i>Inscription réservée aux comptes apprenti
                </div>
              @endif
            @else
              <a href="{{ route('login') }}" class="btn-or w-100"
                 style="padding:14px;font-size:15px;display:block;text-align:center;">
                <i class="bi bi-person me-2"></i>Connexion pour s'inscrire
              </a>
            @endauth
          @endif

          <p style="font-size:12px;color:var(--gris-doux);text-align:center;margin-top:12px;">
            <i class="bi bi-shield-check me-1 text-success"></i>
            Inscription gratuite — Annulation possible
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const tabs = document.querySelectorAll('.tab-nav a');
  tabs.forEach(tab => {
    tab.addEventListener('click', e => {
      e.preventDefault();
      const target = tab.getAttribute('data-tab');
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      document.querySelectorAll('[id^="tab-"]').forEach(panel => {
        panel.style.display = panel.id === 'tab-' + target ? '' : 'none';
      });
    });
  });
</script>
@endpush