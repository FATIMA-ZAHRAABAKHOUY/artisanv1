@extends('layouts.app')
@section('title', 'Fournisseurs — Admin')

@push('styles')
@include('admin.partials.layout-styles')
<style>
.fournisseur-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.fstat {
    background: white;
    border-radius: var(--radius);
    border: 1px solid var(--sable-dark);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.fstat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.fstat.total::before   { background: var(--or); }
.fstat.actifs::before  { background: var(--vert-atlas); }
.fstat.attente::before { background: #D97706; }
.fstat-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.fstat.total .fstat-icon   { background: rgba(155,74,58,0.10); color: var(--or); }
.fstat.actifs .fstat-icon  { background: rgba(74,103,65,0.10);  color: var(--vert-atlas); }
.fstat.attente .fstat-icon { background: rgba(217,119,6,0.10);  color: #D97706; }
.fstat-val {
    font-family: var(--font-serif);
    font-size: 30px;
    font-weight: 700;
    color: var(--texte);
    line-height: 1;
}
.fstat-lbl {
    font-size: 12.5px;
    color: var(--gris-doux);
    margin-top: 3px;
}
@media (max-width: 768px) {
    .fournisseur-stats { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        {{-- ── En-tête ───────────────────────────────────────────────────── --}}
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div class="section-header mb-0">
                <div class="section-icon"><i class="bi bi-building"></i></div>
                <div>
                    <h2>Fournisseurs</h2>
                    <p>Gérez les fournisseurs de la coopérative</p>
                </div>
            </div>
            <a href="{{ route('admin.fournisseurs.create') }}" class="btn-or btn">
                <i class="bi bi-plus-lg me-1"></i>Ajouter un fournisseur
            </a>
        </div>

        {{-- ── KPI ──────────────────────────────────────────────────────── --}}
        <div class="fournisseur-stats">
            <div class="fstat total">
                <div class="fstat-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="fstat-val">{{ $total }}</div>
                    <div class="fstat-lbl">Total fournisseurs</div>
                </div>
            </div>
            <div class="fstat actifs">
                <div class="fstat-icon"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="fstat-val">{{ $totalActifs }}</div>
                    <div class="fstat-lbl">Actifs</div>
                </div>
            </div>
            <div class="fstat attente">
                <div class="fstat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="fstat-val">{{ $enAttente }}</div>
                    <div class="fstat-lbl">En attente de validation</div>
                </div>
            </div>
        </div>

        {{-- ── Bannière en attente ───────────────────────────────────────── --}}
        @if($enAttente > 0)
            <div class="alert-tissu warning d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                <span>
                    <i class="bi bi-hourglass-split me-2"></i>
                    <strong>{{ $enAttente }}</strong> fournisseur(s) en attente de validation
                </span>
                <a href="{{ route('admin.fournisseurs.index', ['statut' => 'inactif']) }}" class="btn btn-sm btn-or">
                    Voir les demandes
                </a>
            </div>
        @endif

        {{-- ── Filtres ───────────────────────────────────────────────────── --}}
        <div class="card-tissu mb-4" style="padding:16px 20px;">
            <form method="GET" action="{{ route('admin.fournisseurs.index') }}"
                  class="d-flex flex-wrap gap-3 align-items-end">
                <div>
                    <label class="form-label-tissu">Recherche</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control form-control-tissu"
                           placeholder="Nom du fournisseur…"
                           style="min-width:200px;">
                </div>
                <div>
                    <label class="form-label-tissu">Type</label>
                    <select name="type" class="form-select form-control-tissu" style="width:160px;">
                        <option value="">Tous les types</option>
                        @foreach(['local' => 'Local', 'national' => 'National', 'en_ligne' => 'En ligne'] as $v => $l)
                            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label-tissu">Statut</label>
                    <select name="statut" class="form-select form-control-tissu" style="width:140px;">
                        <option value="">Tous</option>
                        @foreach(['actif' => 'Actif', 'inactif' => 'Inactif'] as $v => $l)
                            <option value="{{ $v }}" {{ request('statut') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-or">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    @if(request()->anyFilled(['q', 'type', 'statut']))
                        <a href="{{ route('admin.fournisseurs.index') }}" class="btn btn-sm btn-outline-or">
                            <i class="bi bi-x me-1"></i>Effacer
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ── Table ─────────────────────────────────────────────────────── --}}
        <div class="card-tissu" style="padding:0;overflow:hidden;">
            <table class="table table-tissu mb-0">
                <thead>
                    <tr>
                        <th style="width:56px;">Logo</th>
                        <th>Fournisseur</th>
                        <th>Type</th>
                        <th>Ville</th>
                        <th>Remise</th>
                        <th>Accès</th>
                        <th>Statut</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $f)
                    <tr>
                        {{-- Logo --}}
                        <td>
                            @if($f->getLogoUrl())
                                <img src="{{ $f->getLogoUrl() }}" alt=""
                                     style="width:40px;height:40px;border-radius:var(--radius-sm);
                                            object-fit:cover;border:1px solid var(--sable-dark);">
                            @else
                                <div style="width:40px;height:40px;border-radius:var(--radius-sm);
                                            background:linear-gradient(135deg,var(--sable),var(--sable-dark));
                                            display:flex;align-items:center;justify-content:center;
                                            font-weight:700;font-size:15px;color:var(--or-dark);">
                                    {{ strtoupper(substr($f->nom, 0, 1)) }}
                                </div>
                            @endif
                        </td>

                        {{-- Nom + email --}}
                        <td>
                            <div style="font-weight:600;color:var(--texte);">{{ $f->nom }}</div>
                            @if($f->email)
                                <div style="font-size:12px;color:var(--gris-doux);">
                                    <i class="bi bi-envelope me-1"></i>{{ $f->email }}
                                </div>
                            @endif
                        </td>

                        {{-- Type --}}
                        <td>
                            @php
                                $types = [
                                    'local'    => ['bi-shop',  'Local',    'var(--sable)',  'var(--texte)'],
                                    'national' => ['bi-truck', 'National', '#EDE9FE',       '#5B21B6'],
                                    'en_ligne' => ['bi-globe', 'En ligne', '#DBEAFE',       '#1E40AF'],
                                ];
                                [$ico, $lbl, $bg, $col] = $types[$f->type] ?? ['bi-building', $f->getTypeLabel(), 'var(--sable)', 'var(--texte)'];
                            @endphp
                            <span class="badge-statut" style="background:{{ $bg }};color:{{ $col }};">
                                <i class="bi {{ $ico }} me-1"></i>{{ $lbl }}
                            </span>
                        </td>

                        {{-- Ville --}}
                        <td style="color:var(--gris-doux);">
                            @if($f->ville)
                                <i class="bi bi-geo-alt me-1"></i>{{ $f->ville }}
                            @else
                                <span style="color:var(--sable-dark);">—</span>
                            @endif
                        </td>

                        {{-- Remise --}}
                        <td>
                            @if($f->remise_cooperative > 0)
                                <span style="color:var(--vert-atlas);font-weight:700;">
                                    {{ $f->remise_cooperative }}%
                                </span>
                            @else
                                <span style="color:var(--sable-dark);">—</span>
                            @endif
                        </td>

                        {{-- Accès --}}
                        <td>
                            @if($f->user_id)
                                <span class="badge-statut badge-actif">
                                    <i class="bi bi-person-check me-1"></i>Actif
                                </span>
                            @else
                                <span class="badge-statut badge-inactif">
                                    <i class="bi bi-person-x me-1"></i>Sans accès
                                </span>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td>
                            @if($f->statut === 'inactif' && $f->user_id)
                                <span class="badge-statut badge-pending">En attente</span>
                            @elseif($f->statut === 'actif')
                                <span class="badge-statut badge-actif">Actif</span>
                            @else
                                <span class="badge-statut badge-inactif">Inactif</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                @if($f->statut === 'inactif')
                                    <form method="POST"
                                          action="{{ route('admin.fournisseurs.activer', $f->id) }}">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-or" title="Activer">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.fournisseurs.edit', $f->id) }}"
                                   class="btn btn-sm btn-outline-or" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.fournisseurs.produits', $f->id) }}"
                                   class="btn btn-sm btn-indigo" title="Produits">
                                    <i class="bi bi-grid"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.fournisseurs.destroy', $f->id) }}"
                                      onsubmit="return confirm('Supprimer définitivement ce fournisseur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                            style="border:1.5px solid var(--rouge-fes);
                                                   color:var(--rouge-fes);background:transparent;"
                                            title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:60px 20px;">
                            <i class="bi bi-building"
                               style="font-size:44px;display:block;margin-bottom:14px;
                                      color:var(--sable-dark);opacity:0.6;"></i>
                            <div style="font-size:15px;font-weight:600;color:var(--texte);">
                                Aucun fournisseur trouvé
                            </div>
                            @if(request()->anyFilled(['q', 'type', 'statut']))
                                <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">
                                    Essayez de modifier vos filtres
                                </div>
                                <a href="{{ route('admin.fournisseurs.index') }}"
                                   class="btn btn-sm btn-outline-or mt-3">
                                    Effacer les filtres
                                </a>
                            @else
                                <a href="{{ route('admin.fournisseurs.create') }}"
                                   class="btn btn-sm btn-or mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter le premier fournisseur
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ────────────────────────────────────────────────── --}}
        @if($fournisseurs->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $fournisseurs->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
