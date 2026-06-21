@extends('layouts.app')
@section('title', 'Fournisseurs — Admin')

@push('styles')
@include('admin.partials.layout-styles')
@endpush

@section('content')
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0;">Fournisseurs</h1>
            <a href="{{ route('admin.fournisseurs.create') }}" class="btn-or btn btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Ajouter un fournisseur
            </a>
        </div>

        @if(session('success'))
            <div class="alert-tissu success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-tissu danger mb-3">{{ session('error') }}</div>
        @endif

        @if($enAttente > 0)
            <div class="alert-tissu warning mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>
                    <i class="bi bi-hourglass-split me-2"></i>
                    <strong>{{ $enAttente }}</strong> fournisseur(s) en attente de validation
                </span>
                <a href="{{ route('admin.fournisseurs.index', ['statut' => 'inactif']) }}" class="btn-or btn btn-sm">
                    Voir les demandes
                </a>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.fournisseurs.index') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label>Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-tissu" placeholder="Nom du fournisseur…" style="min-width:200px;">
            </div>
            <div>
                <label>Type</label>
                <select name="type" class="form-select form-control-tissu" style="width:160px;">
                    <option value="">Tous</option>
                    @foreach(['local'=>'Local','national'=>'National','en_ligne'=>'En ligne'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('type')===$v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Statut</label>
                <select name="statut" class="form-select form-control-tissu" style="width:140px;">
                    <option value="">Tous</option>
                    @foreach(['actif'=>'Actif','inactif'=>'Inactif'] as $v=>$l)
                        <option value="{{ $v }}" {{ request('statut')===$v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-indigo btn btn-sm">Filtrer</button>
        </form>

        <div class="card-tissu" style="padding:0;overflow:hidden;">
            <table class="table table-tissu mb-0">
                <thead>
                    <tr>
                        <th style="width:56px;">Logo</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Ville</th>
                        <th>Remise coop.</th>
                        <th>Accès</th>
                        <th>Statut</th>
                        <th style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $f)
                    <tr>
                        <td>
                            @if($f->getLogoUrl())
                                <img src="{{ $f->getLogoUrl() }}" alt="" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                            @else
                                <div style="width:40px;height:40px;border-radius:8px;background:var(--sable-dark);
                                            display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--or-dark);">
                                    {{ strtoupper(substr($f->nom, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600;">{{ $f->nom }}</div>
                            @if($f->email)<div style="font-size:12px;color:var(--gris-doux);">{{ $f->email }}</div>@endif
                        </td>
                        <td><span class="badge-statut" style="background:var(--sable);color:var(--texte);">{{ $f->getTypeLabel() }}</span></td>
                        <td style="color:var(--gris-doux);">{{ $f->ville ?? '—' }}</td>
                        <td>
                            @if($f->remise_cooperative > 0)
                                <span style="color:var(--vert-atlas);font-weight:600;">{{ $f->remise_cooperative }}%</span>
                            @else
                                <span style="color:var(--gris-doux);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($f->user_id)
                                <span style="font-size:11px;background:#d1fae5;color:#065f46;
                                             border-radius:20px;padding:2px 8px;">
                                    <i class="bi bi-person-check"></i> Accès actif
                                </span>
                            @else
                                <span style="font-size:11px;background:var(--sable);color:var(--gris-doux);
                                             border-radius:20px;padding:2px 8px;">
                                    <i class="bi bi-person-x"></i> Sans accès
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($f->statut === 'inactif' && $f->user_id)
                                <span class="badge-statut badge-pending">En attente</span>
                            @else
                                <span class="badge-statut badge-{{ $f->statut === 'actif' ? 'confirmed' : 'cancelled' }}">{{ ucfirst($f->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if($f->statut === 'inactif')
                                    <form method="POST" action="{{ route('admin.fournisseurs.activer', $f->id) }}">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-or">Activer</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.fournisseurs.edit', $f->id) }}" class="btn btn-sm btn-outline-or">Modifier</a>
                                <a href="{{ route('admin.fournisseurs.produits', $f->id) }}" class="btn btn-sm btn-indigo">Produits</a>
                                <form method="POST" action="{{ route('admin.fournisseurs.destroy', $f->id) }}"
                                      onsubmit="return confirm('Supprimer définitivement ce fournisseur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="border:1px solid var(--rouge-fes);color:var(--rouge-fes);background:transparent;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--gris-doux);">Aucun fournisseur trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $fournisseurs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
