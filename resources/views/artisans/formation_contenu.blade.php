@extends('layouts.app')
@section('title', 'Gérer le contenu — ' . $formation->titre)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item"><a href="{{ route('artisan.formations') }}">Mes formations</a></li>
  <li class="breadcrumb-item active">Contenu — {{ Str::limit($formation->titre, 40) }}</li>
@endsection


@section('content')
@php
  $typesMateriau = [
    'fil' => 'Fil', 'laine' => 'Laine', 'coton' => 'Coton', 'soie' => 'Soie',
    'lin' => 'Lin', 'raphia' => 'Raphia', 'corde' => 'Corde', 'autre' => 'Autre',
  ];
  $unitesMateriau = [
    'metre' => 'Mètre', 'gramme' => 'Gramme', 'kilogramme' => 'Kilogramme',
    'pelote' => 'Pelote', 'bobine' => 'Bobine', 'piece' => 'Pièce', 'autre' => 'Autre',
  ];
@endphp

<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-journal-text"></i></div>
        <div>
          <h2>{{ $formation->titre }}</h2>
          <p>Gérer le programme, les matériaux, outils et ressources</p>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('formations.show', $formation->id) }}" class="btn-outline-or" target="_blank">
          <i class="bi bi-eye me-1"></i>Aperçu public
        </a>
        <a href="{{ route('artisan.formations') }}" class="btn-outline-or">
          <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-4"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert-tissu danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    <div class="tab-nav">
      <a href="#description" class="active" data-tab="description">📋 Description</a>
      <a href="#programme" data-tab="programme">📚 Programme ({{ $formation->etapes->count() }})</a>
      <a href="#materiaux" data-tab="materiaux">🧵 Matériaux ({{ $formation->materiaux->count() }})</a>
      <a href="#outils" data-tab="outils">🔧 Outils ({{ $formation->outils->count() }})</a>
      <a href="#ressources" data-tab="ressources">📹 Ressources ({{ $formation->ressources->count() }})</a>
    </div>

    {{-- Description (lecture seule) --}}
    <div id="tab-description">
      <div class="card-tissu p-4">
        <p style="font-size:15px;line-height:1.85;color:var(--gris-doux);margin-bottom:16px;">
          {{ $formation->description ?: 'Aucune description renseignée.' }}
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:14px;color:var(--gris-doux);">
          <span><i class="bi bi-calendar3 me-1"></i>{{ $formation->date_debut?->format('d/m/Y') }} — {{ $formation->date_fin?->format('d/m/Y') }}</span>
          <span><i class="bi bi-geo-alt me-1"></i>{{ $formation->lieu ?: '—' }}</span>
          <span><i class="bi bi-cash me-1"></i>{{ $formation->prix == 0 ? 'Gratuit' : number_format($formation->prix, 0) . ' MAD' }}</span>
        </div>
        <a href="{{ route('artisan.formations.edit', $formation->id) }}" class="btn-outline-or mt-3" style="display:inline-block;">
          Modifier les informations générales
        </a>
      </div>
    </div>

    {{-- Programme --}}
    <div id="tab-programme" style="display:none;">
      @forelse($formation->etapes as $etape)
        <div class="card-tissu p-3 mb-2" id="etape-{{ $etape->id }}">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
              <strong>{{ $etape->numero_ordre }}. {{ $etape->titre }}</strong>
              @if($etape->description)
                <p style="font-size:13px;color:var(--gris-doux);margin:4px 0;">{{ $etape->description }}</p>
              @endif
              @if($etape->duree_minutes)
                <span style="font-size:12px;background:var(--sable);padding:2px 8px;border-radius:12px;">
                  {{ $etape->duree_minutes }} min
                </span>
              @endif
              @if($etape->objectif)
                <span style="font-size:12px;color:var(--vert-atlas);margin-left:8px;">
                  <i class="bi bi-bullseye"></i> {{ $etape->objectif }}
                </span>
              @endif
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
              <button type="button" class="btn-outline-or" style="padding:5px 10px;font-size:12px;"
                      onclick="document.getElementById('edit-etape-{{ $etape->id }}').style.display='block'">
                Modifier
              </button>
              <form method="POST" action="{{ route('artisan.formations.etapes.destroy', [$formation->id, $etape->id]) }}">
                @csrf @method('DELETE')
                <button type="submit" style="padding:5px 10px;font-size:12px;background:none;
                        border:1px solid var(--rouge-fes);color:var(--rouge-fes);border-radius:6px;"
                        onclick="return confirm('Supprimer cette étape ?')">
                  Supprimer
                </button>
              </form>
            </div>
          </div>

          <div id="edit-etape-{{ $etape->id }}" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--sable-dark);">
            <form method="POST" action="{{ route('artisan.formations.etapes.update', [$formation->id, $etape->id]) }}">
              @csrf @method('PUT')
              <div class="row g-2 mb-2">
                <div class="col-2">
                  <input type="number" name="numero_ordre" value="{{ $etape->numero_ordre }}"
                         class="form-control-tissu" placeholder="N°" min="1" required>
                </div>
                <div class="col-10">
                  <input type="text" name="titre" value="{{ $etape->titre }}"
                         class="form-control-tissu" placeholder="Titre" required>
                </div>
              </div>
              <textarea name="description" class="form-control-tissu mb-2" rows="2"
                        placeholder="Description">{{ $etape->description }}</textarea>
              <div class="row g-2 mb-2">
                <div class="col-6">
                  <input type="number" name="duree_minutes" value="{{ $etape->duree_minutes }}"
                         class="form-control-tissu" placeholder="Durée (min)">
                </div>
                <div class="col-6">
                  <input type="text" name="objectif" value="{{ $etape->objectif }}"
                         class="form-control-tissu" placeholder="Objectif">
                </div>
              </div>
              <textarea name="materiaux_requis" class="form-control-tissu mb-2" rows="2"
                        placeholder="Matériaux requis pour cette étape">{{ $etape->materiaux_requis }}</textarea>
              <button type="submit" class="btn-or" style="padding:7px 16px;font-size:13px;">Enregistrer</button>
            </form>
          </div>
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucune étape pour le moment.</p>
      @endforelse

      <div class="card-tissu p-3 mt-3" style="background:var(--sable);">
        <h5 style="font-size:14px;margin-bottom:12px;">Ajouter une étape</h5>
        <form method="POST" action="{{ route('artisan.formations.etapes.store', $formation->id) }}">
          @csrf
          <div class="row g-2 mb-2">
            <div class="col-2">
              <input type="number" name="numero_ordre" class="form-control-tissu"
                     placeholder="N°" min="1" value="{{ $formation->etapes->count() + 1 }}" required>
            </div>
            <div class="col-10">
              <input type="text" name="titre" class="form-control-tissu" placeholder="Titre de l'étape" required>
            </div>
          </div>
          <textarea name="description" class="form-control-tissu mb-2" rows="2"
                    placeholder="Description détaillée"></textarea>
          <div class="row g-2 mb-2">
            <div class="col-6">
              <input type="number" name="duree_minutes" class="form-control-tissu" placeholder="Durée (minutes)">
            </div>
            <div class="col-6">
              <input type="text" name="objectif" class="form-control-tissu" placeholder="Objectif pédagogique">
            </div>
          </div>
          <textarea name="materiaux_requis" class="form-control-tissu mb-2" rows="2"
                    placeholder="Matériaux requis pour cette étape"></textarea>
          <button type="submit" class="btn-or" style="padding:8px 18px;font-size:13px;">
            <i class="bi bi-plus-circle me-2"></i>Ajouter
          </button>
        </form>
      </div>
    </div>

    {{-- Matériaux --}}
    <div id="tab-materiaux" style="display:none;">
      @forelse($formation->materiaux as $mat)
        <div class="card-tissu p-3 mb-2">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="display:flex;gap:12px;flex:1;">
              @if($mat->image)
                <img src="{{ asset('storage/'.$mat->image) }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
              @endif
              <div>
                <strong>{{ $mat->nom }}</strong>
                <span style="font-size:12px;background:var(--sable);padding:2px 8px;border-radius:12px;margin-left:6px;">{{ $mat->type }}</span>
                <p style="font-size:13px;color:var(--gris-doux);margin:4px 0;">
                  {{ $mat->quantite }} {{ $mat->unite }}
                  @if($mat->couleur) — {{ $mat->couleur }} @endif
                </p>
                @if($mat->description)<p style="font-size:13px;margin:0;">{{ $mat->description }}</p>@endif
                <span style="font-size:12px;color:{{ $mat->est_fourni ? 'var(--vert-atlas)' : 'var(--or-dark)' }};">
                  {{ $mat->est_fourni ? '✅ Fourni par l\'artisan' : '🎒 À apporter' }}
                </span>
              </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
              <button type="button" class="btn-outline-or" style="padding:5px 10px;font-size:12px;"
                      onclick="document.getElementById('edit-mat-{{ $mat->id }}').style.display='block'">Modifier</button>
              <form method="POST" action="{{ route('artisan.formations.materiaux.destroy', [$formation->id, $mat->id]) }}">
                @csrf @method('DELETE')
                <button type="submit" style="padding:5px 10px;font-size:12px;background:none;border:1px solid var(--rouge-fes);color:var(--rouge-fes);border-radius:6px;"
                        onclick="return confirm('Supprimer ce matériau ?')">Supprimer</button>
              </form>
            </div>
          </div>

          <div id="edit-mat-{{ $mat->id }}" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--sable-dark);">
            <form method="POST" action="{{ route('artisan.formations.materiaux.update', [$formation->id, $mat->id]) }}" enctype="multipart/form-data">
              @csrf @method('PUT')
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <input type="text" name="nom" value="{{ $mat->nom }}" class="form-control-tissu" placeholder="Nom" required>
                </div>
                <div class="col-md-3">
                  <select name="type" class="form-select-tissu" required>
                    @foreach($typesMateriau as $val => $lbl)
                      <option value="{{ $val }}" @selected($mat->type === $val)>{{ $lbl }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <input type="text" name="couleur" value="{{ $mat->couleur }}" class="form-control-tissu" placeholder="Couleur">
                </div>
              </div>
              <div class="row g-2 mb-2">
                <div class="col-4">
                  <input type="number" name="quantite" value="{{ $mat->quantite }}" step="0.01" min="0" class="form-control-tissu" placeholder="Quantité" required>
                </div>
                <div class="col-4">
                  <select name="unite" class="form-select-tissu" required>
                    @foreach($unitesMateriau as $val => $lbl)
                      <option value="{{ $val }}" @selected($mat->unite === $val)>{{ $lbl }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-4 d-flex align-items-center">
                  <label class="d-flex align-items-center gap-2" style="font-size:13px;">
                    <input type="checkbox" name="est_fourni" value="1" @checked($mat->est_fourni)> Fourni par l'artisan
                  </label>
                </div>
              </div>
              <textarea name="description" class="form-control-tissu mb-2" rows="2" placeholder="Description">{{ $mat->description }}</textarea>
              <input type="file" name="image" class="form-control-tissu mb-2" accept="image/*">
              <button type="submit" class="btn-or" style="padding:7px 16px;font-size:13px;">Enregistrer</button>
            </form>
          </div>
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucun matériau pour le moment.</p>
      @endforelse

      <div class="card-tissu p-3 mt-3" style="background:var(--sable);">
        <h5 style="font-size:14px;margin-bottom:12px;">Ajouter un matériau</h5>
        <form method="POST" action="{{ route('artisan.formations.materiaux.store', $formation->id) }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <input type="text" name="nom" class="form-control-tissu" placeholder="Nom du matériau" required>
            </div>
            <div class="col-md-3">
              <select name="type" class="form-select-tissu" required>
                @foreach($typesMateriau as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <input type="text" name="couleur" class="form-control-tissu" placeholder="Couleur">
            </div>
          </div>
          <div class="row g-2 mb-2">
            <div class="col-3">
              <input type="number" name="quantite" step="0.01" min="0" class="form-control-tissu" placeholder="Quantité" required>
            </div>
            <div class="col-3">
              <select name="unite" class="form-select-tissu" required>
                @foreach($unitesMateriau as $val => $lbl)
                  <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-2">
              <input type="number" name="ordre" class="form-control-tissu" placeholder="Ordre"
                     value="{{ $formation->materiaux->count() + 1 }}" min="0">
            </div>
            <div class="col-4 d-flex align-items-center">
              <label class="d-flex align-items-center gap-2" style="font-size:13px;">
                <input type="checkbox" name="est_fourni" value="1"> Fourni par l'artisan
              </label>
            </div>
          </div>
          <textarea name="description" class="form-control-tissu mb-2" rows="2" placeholder="Description"></textarea>
          <input type="file" name="image" class="form-control-tissu mb-2" accept="image/*">
          <button type="submit" class="btn-or" style="padding:8px 18px;font-size:13px;">
            <i class="bi bi-plus-circle me-2"></i>Ajouter
          </button>
        </form>
      </div>
    </div>

    {{-- Outils --}}
    <div id="tab-outils" style="display:none;">
      @forelse($formation->outils as $outil)
        <div class="card-tissu p-3 mb-2">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="display:flex;gap:12px;flex:1;">
              @if($outil->image)
                <img src="{{ asset('storage/'.$outil->image) }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
              @endif
              <div>
                <strong>{{ $outil->nom }}</strong>
                <span style="font-size:12px;margin-left:6px;">× {{ $outil->quantite }}</span>
                @if($outil->description)
                  <p style="font-size:13px;color:var(--gris-doux);margin:4px 0;">{{ $outil->description }}</p>
                @endif
                <span style="font-size:12px;color:{{ $outil->est_fourni ? 'var(--vert-atlas)' : 'var(--or-dark)' }};">
                  {{ $outil->est_fourni ? '✅ Fourni' : '🎒 À apporter' }}
                </span>
                @if($outil->lien_achat)
                  <a href="{{ $outil->lien_achat }}" target="_blank" style="font-size:12px;display:block;margin-top:4px;">Lien d'achat</a>
                @endif
              </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
              <button type="button" class="btn-outline-or" style="padding:5px 10px;font-size:12px;"
                      onclick="document.getElementById('edit-outil-{{ $outil->id }}').style.display='block'">Modifier</button>
              <form method="POST" action="{{ route('artisan.formations.outils.destroy', [$formation->id, $outil->id]) }}">
                @csrf @method('DELETE')
                <button type="submit" style="padding:5px 10px;font-size:12px;background:none;border:1px solid var(--rouge-fes);color:var(--rouge-fes);border-radius:6px;"
                        onclick="return confirm('Supprimer cet outil ?')">Supprimer</button>
              </form>
            </div>
          </div>

          <div id="edit-outil-{{ $outil->id }}" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid var(--sable-dark);">
            <form method="POST" action="{{ route('artisan.formations.outils.update', [$formation->id, $outil->id]) }}" enctype="multipart/form-data">
              @csrf @method('PUT')
              <div class="row g-2 mb-2">
                <div class="col-md-8">
                  <input type="text" name="nom" value="{{ $outil->nom }}" class="form-control-tissu" placeholder="Nom" required>
                </div>
                <div class="col-md-4">
                  <input type="number" name="quantite" value="{{ $outil->quantite }}" min="1" class="form-control-tissu" placeholder="Quantité" required>
                </div>
              </div>
              <textarea name="description" class="form-control-tissu mb-2" rows="2" placeholder="Description">{{ $outil->description }}</textarea>
              <div class="row g-2 mb-2">
                <div class="col-md-8">
                  <input type="url" name="lien_achat" value="{{ $outil->lien_achat }}" class="form-control-tissu" placeholder="Lien d'achat (URL)">
                </div>
                <div class="col-md-4 d-flex align-items-center">
                  <label class="d-flex align-items-center gap-2" style="font-size:13px;">
                    <input type="checkbox" name="est_fourni" value="1" @checked($outil->est_fourni)> Fourni
                  </label>
                </div>
              </div>
              <input type="file" name="image" class="form-control-tissu mb-2" accept="image/*">
              <button type="submit" class="btn-or" style="padding:7px 16px;font-size:13px;">Enregistrer</button>
            </form>
          </div>
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucun outil pour le moment.</p>
      @endforelse

      <div class="card-tissu p-3 mt-3" style="background:var(--sable);">
        <h5 style="font-size:14px;margin-bottom:12px;">Ajouter un outil</h5>
        <form method="POST" action="{{ route('artisan.formations.outils.store', $formation->id) }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-2 mb-2">
            <div class="col-md-8">
              <input type="text" name="nom" class="form-control-tissu" placeholder="Nom de l'outil" required>
            </div>
            <div class="col-md-2">
              <input type="number" name="quantite" min="1" value="1" class="form-control-tissu" placeholder="Qté" required>
            </div>
            <div class="col-md-2">
              <input type="number" name="ordre" class="form-control-tissu" placeholder="Ordre"
                     value="{{ $formation->outils->count() + 1 }}" min="0">
            </div>
          </div>
          <textarea name="description" class="form-control-tissu mb-2" rows="2" placeholder="Description"></textarea>
          <div class="row g-2 mb-2">
            <div class="col-md-8">
              <input type="url" name="lien_achat" class="form-control-tissu" placeholder="Lien d'achat (URL)">
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <label class="d-flex align-items-center gap-2" style="font-size:13px;">
                <input type="checkbox" name="est_fourni" value="1"> Fourni par l'artisan
              </label>
            </div>
          </div>
          <input type="file" name="image" class="form-control-tissu mb-2" accept="image/*">
          <button type="submit" class="btn-or" style="padding:8px 18px;font-size:13px;">
            <i class="bi bi-plus-circle me-2"></i>Ajouter
          </button>
        </form>
      </div>
    </div>

    {{-- Ressources --}}
    <div id="tab-ressources" style="display:none;">
      @forelse($formation->ressources as $res)
        <div class="card-tissu p-3 mb-2">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="flex:1;">
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:20px;">
                  @if($res->type==='video') 🎬
                  @elseif($res->type==='document_pdf') 📄
                  @elseif($res->type==='image') 🖼️
                  @else 🔗 @endif
                </span>
                <strong>{{ $res->titre }}</strong>
                @if($res->isUploadedFile())
                  <span style="font-size:10px;background:#d1fae5;color:#065f46;border-radius:10px;padding:1px 6px;">
                    Fichier uploadé
                  </span>
                @else
                  <span style="font-size:10px;background:#dbeafe;color:#1e40af;border-radius:10px;padding:1px 6px;">
                    Lien externe
                  </span>
                @endif
                @if(!$res->est_public)
                  <span style="font-size:10px;background:#fef3c7;color:#92400e;border-radius:10px;padding:1px 6px;">
                    Privé
                  </span>
                @endif
              </div>
              @if($res->description)
                <p style="font-size:13px;color:var(--gris-doux);margin:6px 0 0;">{{ $res->description }}</p>
              @endif
              <a href="{{ $res->url_complete }}" target="_blank" style="font-size:12px;color:var(--or-dark);">
                Voir/Télécharger →
              </a>
              @if($res->taille_ko)
                <span style="font-size:11px;color:var(--gris-doux);margin-left:8px;">
                  ({{ number_format($res->taille_ko) }} Ko)
                </span>
              @endif
            </div>
            <form method="POST" action="{{ route('artisan.formations.ressources.destroy', [$formation->id, $res->id]) }}">
              @csrf @method('DELETE')
              <button type="submit" style="padding:5px 10px;font-size:12px;background:none;
                      border:1px solid var(--rouge-fes);color:var(--rouge-fes);border-radius:6px;"
                      onclick="return confirm('Supprimer cette ressource ? Le fichier sera définitivement effacé.')">
                Supprimer
              </button>
            </form>
          </div>
        </div>
      @empty
        <p style="color:var(--gris-doux);">Aucune ressource pour le moment.</p>
      @endforelse

      <div class="card-tissu p-3 mt-3" style="background:var(--sable);">
        <h5 style="font-size:14px;margin-bottom:12px;">Ajouter une ressource</h5>
        <form method="POST" action="{{ route('artisan.formations.ressources.store', $formation->id) }}"
              enctype="multipart/form-data" id="add-ressource-form">
          @csrf

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label-tissu">Type de ressource *</label>
              <select name="type" id="ressourceType" class="form-control-tissu" required>
                <option value="video">🎬 Vidéo</option>
                <option value="document_pdf">📄 Document PDF</option>
                <option value="image">🖼️ Image</option>
                <option value="lien_externe">🔗 Lien externe</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label-tissu">Titre *</label>
              <input type="text" name="titre" class="form-control-tissu"
                     placeholder="Ex: Démonstration du point compté" required>
            </div>
          </div>

          <textarea name="description" class="form-control-tissu mb-3" rows="2"
                    placeholder="Description (optionnel)"></textarea>

          <div class="mb-3" id="sourceTypeWrapper">
            <label class="form-label-tissu">Source du fichier *</label>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                <input type="radio" name="source_type" value="upload" checked
                       id="sourceUpload" style="accent-color:var(--or);">
                Uploader un fichier
              </label>
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;" id="sourceUrlOptionLabel">
                <input type="radio" name="source_type" value="url"
                       id="sourceUrl" style="accent-color:var(--or);">
                Coller un lien (YouTube, Vimeo...)
              </label>
            </div>
          </div>

          <div id="uploadField" class="mb-3">
            <label class="form-label-tissu">Fichier *</label>
            <input type="file" name="fichier" class="form-control-tissu" id="fichierInput">
            <small style="font-size:11px;color:var(--gris-doux);">
              Vidéo: MP4, MOV, AVI, WebM — PDF: format PDF — Image: JPG, PNG, WebP — Max 50 Mo
            </small>
          </div>

          <div id="urlField" class="mb-3" style="display:none;">
            <label class="form-label-tissu">URL *</label>
            <input type="url" name="url" class="form-control-tissu" id="urlInput"
                   placeholder="https://youtube.com/watch?v=... ou https://...">
          </div>

          <div class="row g-2 mb-3" id="videoExtraFields">
            <div class="col-md-6">
              <label class="form-label-tissu">Durée (en secondes)</label>
              <input type="number" name="duree_secondes" class="form-control-tissu"
                     placeholder="Ex: 720 (12 min)">
            </div>
          </div>

          <div class="row g-2 mb-3" id="pdfExtraFields" style="display:none;">
            <div class="col-md-6">
              <label class="form-label-tissu">Nombre de pages</label>
              <input type="number" name="nb_pages" class="form-control-tissu" placeholder="Ex: 24">
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label-tissu">Auteur</label>
              <input type="text" name="auteur" class="form-control-tissu"
                     value="{{ auth()->user()->nom_complet }}">
            </div>
            <div class="col-md-2">
              <label class="form-label-tissu">Ordre</label>
              <input type="number" name="ordre" class="form-control-tissu"
                     value="{{ $formation->ressources->count() + 1 }}" min="0">
            </div>
            <div class="col-md-6" style="display:flex;align-items:center;padding-top:24px;">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="est_public" value="1" style="accent-color:var(--or);">
                Visible avant inscription (gratuit/public)
              </label>
            </div>
          </div>

          <button type="submit" class="btn-or" style="padding:8px 18px;font-size:13px;">
            <i class="bi bi-cloud-upload me-2"></i>Ajouter la ressource
          </button>
        </form>
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

  function toggleSourceFields() {
    const isUpload = document.getElementById('sourceUpload')?.checked;
    const uploadField = document.getElementById('uploadField');
    const urlField = document.getElementById('urlField');
    const fichierInput = document.getElementById('fichierInput');
    const urlInput = document.getElementById('urlInput');

    if (uploadField) uploadField.style.display = isUpload ? 'block' : 'none';
    if (urlField) urlField.style.display = isUpload ? 'none' : 'block';
    if (fichierInput) fichierInput.required = isUpload;
    if (urlInput) urlInput.required = !isUpload;
  }

  document.querySelectorAll('input[name="source_type"]').forEach(radio => {
    radio.addEventListener('change', toggleSourceFields);
  });

  function updateRessourceTypeFields() {
    const typeSelect = document.getElementById('ressourceType');
    if (!typeSelect) return;

    const type = typeSelect.value;
    const videoFields = document.getElementById('videoExtraFields');
    const pdfFields = document.getElementById('pdfExtraFields');
    const sourceUpload = document.getElementById('sourceUpload');
    const sourceUrl = document.getElementById('sourceUrl');
    const sourceTypeWrapper = document.getElementById('sourceTypeWrapper');
    const fichierInput = document.getElementById('fichierInput');

    if (videoFields) videoFields.style.display = type === 'video' ? 'flex' : 'none';
    if (pdfFields) pdfFields.style.display = type === 'document_pdf' ? 'flex' : 'none';

    if (type === 'lien_externe') {
      if (sourceUrl) {
        sourceUrl.checked = true;
        sourceUrl.disabled = false;
      }
      if (sourceUpload) sourceUpload.disabled = true;
      if (sourceTypeWrapper) sourceTypeWrapper.style.display = 'none';
      toggleSourceFields();
    } else {
      if (sourceUpload) sourceUpload.disabled = false;
      if (sourceTypeWrapper) sourceTypeWrapper.style.display = 'block';
    }

    const acceptMap = {
      video: '.mp4,.mov,.avi,.webm',
      document_pdf: '.pdf',
      image: '.jpg,.jpeg,.png,.webp',
    };
    if (fichierInput) {
      fichierInput.setAttribute('accept', acceptMap[type] || '*');
    }
  }

  document.getElementById('ressourceType')?.addEventListener('change', updateRessourceTypeFields);
  toggleSourceFields();
  updateRessourceTypeFields();
</script>
@endpush
