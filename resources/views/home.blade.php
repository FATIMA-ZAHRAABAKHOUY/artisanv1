@extends('layouts.app')

@section('title', "Accueil — L'Âme du Fil")

@push('styles')
<style>
    /* ── HERO (maquette : photo + overlay gauche) ─────────── */
    .hero {
        position: relative;
        min-height: 88vh;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: #1a1412;
    }

    .hero-visual {
        position: absolute;
        inset: 0;
        left: 28%;
    }

    .hero-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 72% center;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            90deg,
            rgba(26, 18, 16, 0.97) 0%,
            rgba(26, 18, 16, 0.93) 32%,
            rgba(26, 18, 16, 0.72) 48%,
            rgba(26, 18, 16, 0.35) 68%,
            rgba(26, 18, 16, 0.08) 100%
        );
        pointer-events: none;
    }

    .hero-inner {
        position: relative;
        z-index: 2;
        width: 100%;
        padding: 80px 0 100px;
    }

    .hero-content {
        max-width: 560px;
    }

    .hero h1 {
        font-family: var(--font-serif);
        font-size: clamp(2.4rem, 5.5vw, 3.75rem);
        font-weight: 700;
        color: #fff;
        line-height: 1.12;
        margin-bottom: 22px;
        letter-spacing: -0.01em;
    }

    .hero p {
        font-family: var(--font-sans);
        font-size: clamp(1rem, 1.8vw, 1.125rem);
        font-weight: 400;
        color: rgba(255, 255, 255, 0.88);
        max-width: 480px;
        line-height: 1.65;
        margin-bottom: 36px;
    }

    .hero-cta {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn-hero-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 32px;
        background: #631e2b;
        color: #fff;
        border: none;
        border-radius: 50px;
        font-family: var(--font-sans);
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: 0 4px 20px rgba(99, 30, 43, 0.35);
    }

    .btn-hero-primary:hover {
        background: #7a2535;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 24px rgba(99, 30, 43, 0.45);
    }

    .btn-hero-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 13px 32px;
        background: transparent;
        color: #fff;
        border: 1.5px solid rgba(255, 255, 255, 0.85);
        border-radius: 50px;
        font-family: var(--font-sans);
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
    }

    .btn-hero-ghost:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        border-color: #fff;
    }

    .hero-stats-bar {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid var(--sable-dark);
        padding: 28px 0;
        margin-top: -1px;
    }

    .hero-stats {
        display: flex;
        gap: 48px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .hero-stat { text-align: center; }

    .hero-stat .num {
        font-family: var(--font-serif);
        font-size: 2rem;
        color: var(--ame-terre-dark);
        font-weight: 700;
        line-height: 1;
        display: block;
    }

    .hero-stat .lbl {
        font-size: 13px;
        color: var(--gris-doux);
        margin-top: 6px;
        display: block;
    }

    @media (max-width: 991px) {
        .hero-visual { left: 0; }
        .hero-photo { object-position: 60% center; }
        .hero-overlay {
            background: linear-gradient(
                180deg,
                rgba(26, 18, 16, 0.94) 0%,
                rgba(26, 18, 16, 0.88) 45%,
                rgba(26, 18, 16, 0.55) 100%
            );
        }
        .hero-inner { padding: 100px 0 60px; }
    }

    @media (max-width: 576px) {
        .hero-cta { flex-direction: column; align-items: stretch; }
        .btn-hero-primary,
        .btn-hero-ghost { width: 100%; text-align: center; }
    }

    /* ── CATÉGORIES ────────────────────────────────────────── */
    .categories-section { padding: 80px 0; background: var(--sable); }

    .cat-card {
        background: white;
        border-radius: var(--radius);
        padding: 0;
        text-align: center;
        border: 1.5px solid var(--sable-dark);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        color: var(--texte);
        overflow: hidden;
    }

    .cat-card:hover {
        border-color: var(--ame-terre);
        box-shadow: 0 8px 30px rgba(155,74,58,0.18);
        transform: translateY(-4px);
        color: var(--texte);
    }

    .cat-card .cat-thumb {
        aspect-ratio: 4/3;
        overflow: hidden;
        background: var(--sable);
    }

    .cat-card .cat-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .cat-card:hover .cat-thumb img {
        transform: scale(1.06);
    }

    .cat-card .cat-body {
        padding: 16px 14px 18px;
    }

    .cat-card .cat-icon {
        font-size: 44px;
        margin-bottom: 14px;
        display: block;
    }

    .cat-card .cat-name {
        font-family: var(--font-serif);
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .cat-card .cat-count {
        font-size: 12.5px;
        color: var(--gris-doux);
    }

    /* ── PRODUITS VEDETTES ────────────────────────────────── */
    .produits-section { padding: 80px 0; }

    .produit-card {
        background: white;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--sable-dark);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .produit-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .produit-img {
        aspect-ratio: 4/3;
        overflow: hidden;
        background: var(--sable);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        position: relative;
    }

    .produit-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .produit-card:hover .produit-img img { transform: scale(1.05); }

    .produit-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: var(--or);
        color: white;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 20px;
        font-weight: 600;
    }

    .produit-body {
        padding: 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .produit-categorie {
        font-size: 11.5px;
        color: var(--or-dark);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .produit-nom {
        font-family: var(--font-serif);
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.3;
        flex: 1;
    }

    .produit-artisan {
        font-size: 12.5px;
        color: var(--gris-doux);
        margin-bottom: 12px;
    }

    .produit-stars {
        color: var(--or);
        font-size: 13px;
        margin-bottom: 14px;
    }

    .produit-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-top: 1px solid var(--sable-dark);
        background: var(--sable);
    }

    .produit-prix {
        font-family: var(--font-serif);
        font-size: 22px;
        font-weight: 700;
        color: var(--or-dark);
    }

    .produit-prix span {
        font-size: 13px;
        font-weight: 400;
        color: var(--gris-doux);
        font-family: var(--font-sans);
    }

    .btn-panier {
        width: 38px;
        height: 38px;
        background: var(--ame-charbon);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .btn-panier:hover {
        background: var(--or-dark);
        color: white;
        transform: scale(1.1);
    }

    /* ── FORMATIONS ────────────────────────────────────────── */
    .formations-section { padding: 80px 0; background: var(--sable); }

    .formation-card {
        background: white;
        border-radius: var(--radius);
        overflow: hidden;
        border: 1px solid var(--sable-dark);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .formation-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .formation-img {
        height: 180px;
        background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 56px;
        position: relative;
    }

    .formation-body { padding: 20px; }

    .formation-nom {
        font-family: var(--font-serif);
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .formation-meta {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .formation-meta span {
        font-size: 12.5px;
        color: var(--gris-doux);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .formation-places {
        height: 6px;
        background: var(--sable-dark);
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .formation-places-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--vert-atlas), var(--ame-terre));
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    /* ── ARTISANS VEDETTES ────────────────────────────────── */
    .artisans-section { padding: 80px 0; }

    .artisan-card {
        background: white;
        border-radius: var(--radius);
        padding: 28px;
        text-align: center;
        border: 1px solid var(--sable-dark);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .artisan-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .artisan-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--ame-terre), var(--ame-fil-or));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        margin: 0 auto 16px;
        border: 3px solid var(--sable-dark);
    }

    .artisan-nom {
        font-family: var(--font-serif);
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .artisan-spec {
        font-size: 13px;
        color: var(--or-dark);
        margin-bottom: 10px;
    }

    .artisan-stars { color: var(--or); font-size: 13px; margin-bottom: 14px; }

    /* ── BANNIÈRE CTA ──────────────────────────────────────── */
    .cta-section {
        padding: 80px 0;
        background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            45deg, transparent, transparent 20px,
            rgba(255,255,255,0.02) 20px, rgba(255,255,255,0.02) 40px
        );
    }

    .cta-section h2 {
        font-family: var(--font-serif);
        font-size: 42px;
        color: white;
        margin-bottom: 16px;
        position: relative;
    }

    .cta-section p {
        color: rgba(255,255,255,0.8);
        font-size: 16px;
        margin-bottom: 32px;
        position: relative;
    }
</style>
@endpush

@section('content')

{{-- ════════════════════════════════════════════════════════
     HERO — maquette photo artisan + texte à gauche
════════════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="hero-visual" aria-hidden="true">
        <img src="{{ asset('images/hero-artisan.png') }}"
             alt=""
             class="hero-photo"
             loading="eager">
    </div>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container-xl hero-inner">
        <div class="hero-content">
            <h1>L'Art du Fil Marocain à Portée de Main</h1>
            <p>
                Découvrez des tapis, tissus et accessoires façonnés main au cœur du Maroc
            </p>
            <div class="hero-cta">
                <a href="{{ route('catalogue.index') }}" class="btn-hero-primary">
                    Explorer le Catalogue
                </a>
                <a href="{{ route('formations.index') }}" class="btn-hero-ghost">
                    Nos Formations
                </a>
            </div>
        </div>
    </div>
</section>

<div class="hero-stats-bar">
    <div class="container-xl">
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="num">{{ \App\Models\Artisan::where('is_verified',true)->count() }}+</span>
                <span class="lbl">Artisans vérifiés</span>
            </div>
            <div class="hero-stat">
                <span class="num">{{ \App\Models\Produit::where('is_active',true)->count() }}+</span>
                <span class="lbl">Produits artisanaux</span>
            </div>
            <div class="hero-stat">
                <span class="num">{{ \App\Models\Formation::where('is_active',true)->count() }}</span>
                <span class="lbl">Formations actives</span>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     CATÉGORIES
════════════════════════════════════════════════════════ --}}
<section class="categories-section">
    <div class="container-xl">
        <div class="section-header">
            <div class="section-icon"><i class="bi bi-grid-3x3-gap"></i></div>
            <div>
                <h2>Nos Catégories</h2>
                <p>Explorez notre sélection de produits artisanaux marocains</p>
            </div>
        </div>

        <div class="row g-3">
            @php
                $cats = \App\Models\Categorie::whereNull('parent_id')->withCount('produits')->take(8)->get();
            @endphp

            @foreach($cats as $cat)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('catalogue.index', ['categorie_id' => $cat->id]) }}" class="cat-card">
                        <div class="cat-thumb">
                            <img src="{{ $cat->image_url }}"
                                 alt="{{ $cat->nom }} — artisanat marocain"
                                 loading="lazy">
                        </div>
                        <div class="cat-body">
                            <div class="cat-name">{{ $cat->nom }}</div>
                            <div class="cat-count">{{ $cat->produits_count }} produit{{ $cat->produits_count > 1 ? 's' : '' }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     PRODUITS VEDETTES
════════════════════════════════════════════════════════ --}}
<section class="produits-section">
    <div class="container-xl">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-header mb-0">
                <div class="section-icon"><i class="bi bi-star"></i></div>
                <div>
                    <h2>Produits Vedettes</h2>
                    <p>Sélection de nos meilleurs artisans</p>
                </div>
            </div>
            <a href="{{ route('catalogue.index') }}" class="btn-outline-or d-none d-md-flex align-items-center gap-2">
                Voir tout <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-3">
            @foreach(\App\Models\Produit::with(['artisan.user','categorie'])->where('is_active',true)->inRandomOrder()->take(8)->get() as $produit)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="produit-card">
                        <div class="produit-img">
                            @if(!empty($produit->images[0]))
                                <img src="{{ asset('storage/'.$produit->images[0]) }}"
                                     alt="{{ $produit->nom }}" loading="lazy">
                            @else
                                🧵
                            @endif
                            <span class="produit-badge">{{ $produit->categorie?->nom ?? 'Artisanal' }}</span>
                        </div>
                        <div class="produit-body">
                            <div class="produit-categorie">{{ $produit->categorie?->nom }}</div>
                            <div class="produit-nom">
                                <a href="{{ route('catalogue.show', $produit->id) }}"
                                   style="color:inherit;text-decoration:none;">{{ $produit->nom }}</a>
                            </div>
                            <div class="produit-artisan">
                                <i class="bi bi-person me-1"></i>
                                {{ $produit->artisan?->user?->nom_complet }}
                            </div>
                            <div class="produit-stars">
                                @for($s=1;$s<=5;$s++)
                                    <i class="bi bi-star{{ $s <= round($produit->note_moyenne) ? '-fill' : '' }}"></i>
                                @endfor
                                <span style="color:var(--gris-doux);font-size:12px;margin-left:4px;">
                                    ({{ $produit->avis()->count() }})
                                </span>
                            </div>
                        </div>
                        <div class="produit-footer">
                            <div class="produit-prix">
                                {{ number_format($produit->prix, 2) }}
                                <span>MAD</span>
                            </div>
                            <a href="{{ route('panier.ajouter', $produit->id) }}"
                               class="btn-panier" title="Ajouter au panier">
                                <i class="bi bi-bag-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     FORMATIONS
════════════════════════════════════════════════════════ --}}
<section class="formations-section">
    <div class="container-xl">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-header mb-0">
                <div class="section-icon"><i class="bi bi-mortarboard"></i></div>
                <div>
                    <h2>Formations Artisanales</h2>
                    <p>Apprenez les techniques ancestrales du tissu marocain</p>
                </div>
            </div>
            <a href="{{ route('formations.index') }}" class="btn-outline-or d-none d-md-flex align-items-center gap-2">
                Toutes les formations <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @php
            $emojis = ['🧵','🎨','🌿','🪡'];
            $formations = \App\Models\Formation::with(['artisan.user','formateurs.user'])
                ->where('is_active',true)
                ->where('date_debut','>=',now())
                ->take(4)->get();
        @endphp

        <div class="row g-3">
            @foreach($formations as $i => $formation)
                @php
                    $inscrits = $formation->inscriptions()->where('statut_inscription','en_cours')->count();
                    $pct      = $formation->places_max > 0 ? ($inscrits / $formation->places_max) * 100 : 0;
                @endphp
                <div class="col-md-6 col-lg-3">
                    <div class="formation-card h-100">
                        <div class="formation-img">
                            {{ $emojis[$i % count($emojis)] }}
                            @if($formation->prix == 0)
                                <span class="produit-badge">Gratuit</span>
                            @endif
                        </div>
                        <div class="formation-body">
                            <div class="formation-nom">{{ $formation->titre }}</div>
                            <div class="formation-meta">
                                <span><i class="bi bi-calendar3"></i> {{ $formation->date_debut?->format('d/m/Y') }}</span>
                                <span><i class="bi bi-geo-alt"></i> {{ $formation->lieu }}</span>
                                <span><i class="bi bi-person"></i> {{ $formation->artisan?->user?->nom_complet }}</span>
                            </div>

                            {{-- Jauge places --}}
                            <div class="d-flex justify-content-between mb-1" style="font-size:12px;color:var(--gris-doux);">
                                <span>{{ $formation->places_max - $inscrits }} places restantes</span>
                                <span>{{ $inscrits }}/{{ $formation->places_max }}</span>
                            </div>
                            <div class="formation-places">
                                <div class="formation-places-fill" style="width:{{ $pct }}%"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="produit-prix" style="font-size:18px;">
                                    @if($formation->prix == 0)
                                        <span style="color:var(--vert-atlas);">Gratuit</span>
                                    @else
                                        {{ number_format($formation->prix,0) }}
                                        <span>MAD</span>
                                    @endif
                                </div>
                                <a href="{{ route('formations.show', $formation->id) }}"
                                   class="btn-or" style="padding:7px 16px;font-size:13px;">
                                    Voir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     NOS ARTISANS
════════════════════════════════════════════════════════ --}}
<section class="artisans-section">
    <div class="container-xl">
        <div class="section-header">
            <div class="section-icon"><i class="bi bi-people"></i></div>
            <div>
                <h2>Nos Artisans</h2>
                <p>Des maîtres artisans vérifiés par la coopérative</p>
            </div>
        </div>

        <div class="row g-3">
            @foreach(\App\Models\Artisan::with('user')->where('is_verified',true)->take(4)->get() as $artisan)
                <div class="col-6 col-md-3">
                    <div class="artisan-card">
                        <div class="artisan-avatar">
                            {{ substr($artisan->user->prenom, 0, 1) }}
                        </div>
                        <div class="artisan-nom">{{ $artisan->user->nom_complet }}</div>
                        <div class="artisan-spec">{{ $artisan->specialite }}</div>
                        <div class="artisan-stars">
                            @for($s=1;$s<=5;$s++)
                                <i class="bi bi-star{{ $s <= round($artisan->note_moyenne) ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <a href="{{ route('artisans.show', $artisan->id) }}"
                           class="btn-outline-or" style="font-size:13px;padding:7px 18px;">
                            Voir profil
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     CTA BANNIÈRE
════════════════════════════════════════════════════════ --}}
<section class="cta-section">
    <div class="container-xl position-relative">
        <h2>Rejoignez L'Âme du Fil</h2>
        <p>Que vous soyez artisan, apprenant ou passionné de textile marocain,<br>
           notre coopérative vous ouvre ses portes</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('register') }}" class="btn-or" style="padding:13px 30px;font-size:15px;">
                <i class="bi bi-person-plus me-2"></i>S'inscrire gratuitement
            </a>
            <a href="{{ route('catalogue.index') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:13px 30px;
                      border:1.5px solid rgba(255,255,255,0.4);border-radius:var(--radius-sm);
                      color:white;font-size:15px;font-weight:500;text-decoration:none;
                      transition:var(--transition);"
               onmouseover="this.style.background='rgba(255,255,255,0.1)'"
               onmouseout="this.style.background='transparent'">
                <i class="bi bi-grid"></i>Explorer le catalogue
            </a>
        </div>
    </div>
</section>

@endsection