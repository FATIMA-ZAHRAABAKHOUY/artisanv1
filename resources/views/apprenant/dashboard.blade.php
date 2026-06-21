@extends('layouts.app')
@section('title', 'Espace Apprenant — Tissu Artisanal')

@section('content')
<div class="container py-4">
    <div class="row g-4">

        {{-- ═══ COLONNE PRINCIPALE ═══ --}}
        <div class="col-lg-8">

            {{-- HEADER --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--or),var(--or-dark));
                                display:flex;align-items:center;justify-content:center;color:white;font-size:22px;font-weight:700;">
                        {{ strtoupper(substr($user->prenom, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-family:'Amiri',serif;font-size:22px;font-weight:700;">
                            Bonjour, {{ $user->prenom }} 👋
                        </div>
                        <div style="color:var(--gris-doux);font-size:14px;">
                            Espace Apprenant — Tissu Artisanal Marocain
                            <span class="badge-statut badge-confirmed ms-2" style="font-size:11px;">{{ $user->role }}</span>
                        </div>
                    </div>
                </div>
                @include('partials.date-theme-widget')
            </div>

            {{-- KPI CARDS --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:16px;border-top:3px solid var(--or);">
                        <div style="font-size:11px;color:var(--gris-doux);margin-bottom:6px;">Formation active</div>
                        <div class="kpi-counter" style="font-family:'Amiri',serif;font-size:28px;font-weight:700;color:var(--or);" data-target="{{ $formationActive ? 1 : 0 }}">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:16px;border-top:3px solid var(--vert-atlas);">
                        <div style="font-size:11px;color:var(--gris-doux);margin-bottom:6px;">Progression</div>
                        <div class="kpi-counter" style="font-family:'Amiri',serif;font-size:28px;font-weight:700;color:var(--vert-atlas);" data-target="{{ $formationActive?->progression ?? 0 }}" data-suffix="%">0%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:16px;border-top:3px solid var(--indigo);">
                        <div style="font-size:11px;color:var(--gris-doux);margin-bottom:6px;">Ressources</div>
                        <div class="kpi-counter" style="font-family:'Amiri',serif;font-size:28px;font-weight:700;color:var(--indigo);" data-target="{{ $nbRessources }}">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);padding:16px;border-top:3px solid var(--rouge-fes);">
                        <div style="font-size:11px;color:var(--gris-doux);margin-bottom:6px;">Terminées</div>
                        <div class="kpi-counter" style="font-family:'Amiri',serif;font-size:28px;font-weight:700;color:var(--rouge-fes);" data-target="{{ $nbTerminees }}">0</div>
                    </div>
                </div>
            </div>

            {{-- FORMATION EN COURS --}}
            @if($formationActive)
            @php $pct = min(100, max(0, (int) $formationActive->progression)); @endphp
            <div style="background:white;border-left:4px solid var(--or);border-radius:var(--radius);box-shadow:var(--shadow-sm);
                        border:1px solid var(--sable-dark);border-left:4px solid var(--or);padding:24px;margin-bottom:20px;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h2 style="font-family:'Amiri',serif;font-size:20px;margin:0 0 6px;">{{ $formationActive->formation?->titre }}</h2>
                        <div style="font-size:13px;color:var(--gris-doux);">
                            <i class="bi bi-person me-1"></i>{{ $formationActive->formation?->artisan?->user?->nom_complet ?? '—' }}
                            · <i class="bi bi-geo-alt ms-1 me-1"></i>{{ $formationActive->formation?->lieu ?? '—' }}
                        </div>
                        <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">
                            {{ $formationActive->formation?->date_debut?->format('d/m/Y') }} → {{ $formationActive->formation?->date_fin?->format('d/m/Y') }}
                            · <strong style="color:var(--or-dark);">{{ $formationActive->formation?->prix == 0 ? 'Gratuit' : number_format($formationActive->formation?->prix, 0).' MAD' }}</strong>
                        </div>
                    </div>
                    <div style="font-family:'Amiri',serif;font-size:36px;font-weight:700;color:var(--or);">{{ $pct }}%</div>
                </div>

                <div style="height:8px;background:var(--sable-dark);border-radius:4px;overflow:hidden;margin-bottom:16px;">
                    <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--or),var(--vert-atlas));border-radius:4px;transition:width 0.8s ease;"></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div style="background:var(--sable);border-radius:var(--radius-sm);padding:12px;text-align:center;">
                            <div style="font-weight:700;font-size:18px;">{{ $etapesTerminees }}/{{ $etapes->count() }}</div>
                            <div style="font-size:11px;color:var(--gris-doux);">Étapes terminées</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:var(--sable);border-radius:var(--radius-sm);padding:12px;text-align:center;">
                            <div style="font-weight:700;font-size:18px;">{{ $joursRestants }}</div>
                            <div style="font-size:11px;color:var(--gris-doux);">Jours restants</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('formations.show', $formationActive->formation_id) }}" class="btn-indigo btn btn-sm">Voir la formation</a>
                    <a href="{{ route('formations.ressources', $formationActive->formation_id) }}" class="btn-or btn btn-sm">Ressources</a>
                    <form method="POST" action="{{ route('formations.abandonner', $formationActive->id) }}" onsubmit="return confirm('Abandonner cette formation ?')">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Abandonner</button>
                    </form>
                </div>
            </div>

            {{-- PROGRAMME --}}
            @if($etapes->count())
            <div class="card-tissu mb-4" style="padding:20px;">
                <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">Programme de la formation</h3>
                @foreach($etapes as $index => $etape)
                @php
                    $status = $index < $etapesTerminees ? 'done' : ($index == $etapesTerminees ? 'current' : 'upcoming');
                @endphp
                <div class="d-flex align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--sable-dark)!important;">
                    <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;
                        @if($status==='done') background:var(--vert-atlas);color:white;
                        @elseif($status==='current') background:var(--or);color:white;
                        @else background:var(--sable-dark);color:var(--gris-doux); @endif">
                        @if($status==='done') ✓ @else {{ $etape->numero_ordre }} @endif
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-weight:600;">{{ $etape->titre }}</div>
                        <div style="font-size:12px;color:var(--gris-doux);">{{ $etape->duree_minutes }} min</div>
                    </div>
                    <span class="badge-statut badge-{{ $status==='done' ? 'delivered' : ($status==='current' ? 'pending' : 'cancelled') }}" style="font-size:10px;">
                        {{ $status==='done' ? 'Terminée' : ($status==='current' ? 'En cours' : 'À venir') }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            @else
            <div class="card-tissu text-center py-5 mb-4">
                <div style="font-size:48px;margin-bottom:12px;">🎓</div>
                <p style="color:var(--gris-doux);margin-bottom:16px;">Aucune formation en cours</p>
                <a href="{{ route('formations.index') }}" class="btn-or btn">Découvrir les formations</a>
            </div>
            @endif

            {{-- AUTRES FORMATIONS --}}
            @if($autresFormations->count())
            <div class="mb-2">
                <h3 style="font-family:'Amiri',serif;font-size:18px;">Formations disponibles</h3>
                @if($formationActive)
                <p style="font-size:13px;color:var(--gris-doux);">Terminez votre formation active d'abord</p>
                @endif
            </div>
            <div class="row g-3">
                @foreach($autresFormations as $f)
                @php $placesRestantes = max(0, $f->places_max - ($f->inscrits_actifs ?? 0)); @endphp
                <div class="col-md-4">
                    <div style="background:white;border:1px solid var(--sable-dark);border-radius:var(--radius);padding:16px;height:100%;">
                        <div style="font-size:28px;margin-bottom:8px;">🧵</div>
                        <div style="font-weight:600;font-size:14px;margin-bottom:6px;">{{ str($f->titre)->limit(35) }}</div>
                        <div style="font-size:12px;color:var(--gris-doux);">{{ $f->artisan?->user?->nom_complet }}</div>
                        <div style="font-size:12px;color:var(--gris-doux);"><i class="bi bi-geo-alt"></i> {{ $f->lieu }}</div>
                        <div style="font-size:12px;margin:6px 0;">{{ $f->date_debut?->format('d/m/Y') }} · {{ $f->prix == 0 ? 'Gratuit' : number_format($f->prix,0).' MAD' }}</div>
                        <div style="font-size:11px;color:var(--gris-doux);">{{ $placesRestantes }} place(s) restante(s)</div>
                        @if($formationActive)
                            <span class="badge-statut badge-cancelled mt-2 d-inline-block" style="font-size:10px;">Non disponible</span>
                        @else
                            <form method="POST" action="{{ route('formations.inscrire', $f->id) }}" class="mt-2">@csrf
                                <button type="submit" class="btn-or btn btn-sm w-100">S'inscrire</button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ═══ SIDEBAR ═══ --}}
        <div class="col-lg-4">

            {{-- RESSOURCES --}}
            <div class="card-tissu mb-3" style="padding:18px;">
                <h4 style="font-family:'Amiri',serif;font-size:16px;margin-bottom:14px;">Ressources pédagogiques</h4>
                @forelse($ressources as $res)
                @php
                    $iconColor = match($res->type) {
                        'video' => 'var(--indigo)',
                        'document_pdf' => 'var(--rouge-fes)',
                        'image' => 'var(--vert-atlas)',
                        default => 'var(--or)',
                    };
                    $icon = match($res->type) {
                        'video' => 'bi-play-circle',
                        'document_pdf' => 'bi-file-pdf',
                        'image' => 'bi-image',
                        default => 'bi-file-earmark',
                    };
                @endphp
                <div class="d-flex align-items-center gap-2 mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--sable-dark)!important;">
                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $iconColor }}22;color:{{ $iconColor }};
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;">{{ str($res->titre)->limit(30) }}</div>
                        <div style="font-size:11px;color:var(--gris-doux);">
                            @if($res->type === 'video' && $res->duree_secondes) {{ gmdate('i', $res->duree_secondes) }} min @endif
                            @if($res->type === 'document_pdf' && $res->nb_pages) {{ $res->nb_pages }} p. @endif
                        </div>
                    </div>
                    <a href="{{ $res->url_complete ?? '#' }}" target="_blank" class="btn btn-sm btn-outline-or" style="font-size:11px;padding:4px 8px;">
                        {{ in_array($res->type, ['video','image']) ? '👁 Voir' : '⬇ Télécharger' }}
                    </a>
                </div>
                @empty
                <p style="font-size:13px;color:var(--gris-doux);margin:0;">Aucune ressource disponible</p>
                @endforelse
            </div>

            {{-- FOURNISSEURS --}}
            <div class="card-tissu mb-3" style="padding:18px;">
                <h4 style="font-family:'Amiri',serif;font-size:16px;margin-bottom:4px;">Fournisseurs suggérés</h4>
                <p style="font-size:12px;color:var(--gris-doux);margin-bottom:14px;">Matériaux pour cette formation</p>
                @forelse($fournisseurs as $four)
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span style="font-size:20px;">{{ match($four->type ?? '') { 'local'=>'🏪', 'national'=>'🚚', 'en_ligne'=>'🌐', default=>'🏪' } }}</span>
                    <div class="flex-grow-1">
                        <div style="font-size:13px;font-weight:600;">{{ $four->nom }}</div>
                        <div style="font-size:11px;color:var(--gris-doux);">{{ $four->type }} · {{ $four->ville ?? '—' }}</div>
                    </div>
                    @if(($four->remise_cooperative ?? 0) > 0)
                    <span style="color:var(--vert-atlas);font-weight:700;font-size:12px;">-{{ $four->remise_cooperative }}%</span>
                    @endif
                </div>
                @empty
                <p style="font-size:13px;color:var(--gris-doux);">Aucun fournisseur suggéré</p>
                @endforelse
                <a href="{{ route('fournisseurs.index') }}" class="btn-outline-or btn btn-sm w-100 mt-2">Voir tous les fournisseurs</a>
            </div>

            {{-- NOTIFICATIONS --}}
            <div class="card-tissu" style="padding:18px;">
                <h4 style="font-family:'Amiri',serif;font-size:16px;margin-bottom:14px;">Notifications</h4>
                @forelse($notifs as $notif)
                @php $dotColor = match($notif->type) { 'success'=> 'var(--vert-atlas)', 'warn','warning'=> 'var(--or)', default=> 'var(--indigo)' }; @endphp
                <div class="d-flex gap-2 mb-3">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};margin-top:6px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:13px;">{{ $notif->message ?? $notif->titre }}</div>
                        <div style="font-size:11px;color:var(--gris-doux);">{{ $notif->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <p style="font-size:13px;color:var(--gris-doux);margin:0;">Aucune notification</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.kpi-counter').forEach(el => {
    const target = parseInt(el.dataset.target || 0, 10);
    const suffix = el.dataset.suffix || '';
    const duration = 600;
    const steps = 30;
    let current = 0;
    const inc = target / steps;
    const timer = setInterval(() => {
        current += inc;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = Math.round(current) + suffix;
    }, duration / steps);
});
</script>
@endpush
