@extends('layouts.app')
@section('title', 'Formateurs — Admin')

@push('styles')
@include('admin.partials.layout-styles')
@endpush

@section('content')
<div class="admin-layout">
    @include('admin.partials.sidebar')

    <div class="admin-main">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0;">Formateurs</h1>
            <a href="{{ route('admin.formateurs.create') }}" class="btn-or btn btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Ajouter un formateur
            </a>
        </div>

        @if(session('success'))
            <div class="alert-tissu success mb-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-tissu danger mb-3">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.formateurs.index') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end mb-4">
            <div>
                <label>Spécialité</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-tissu"
                       placeholder="Rechercher…" style="min-width:200px;">
            </div>
            <div>
                <label>Type</label>
                <select name="type" class="form-select form-control-tissu" style="width:140px;">
                    <option value="">Tous</option>
                    <option value="interne" {{ request('type')==='interne' ? 'selected':'' }}>Interne</option>
                    <option value="externe" {{ request('type')==='externe' ? 'selected':'' }}>Externe</option>
                </select>
            </div>
            <div>
                <label>Disponibilité</label>
                <select name="disponible" class="form-select form-control-tissu" style="width:140px;">
                    <option value="">Tous</option>
                    <option value="1" {{ request('disponible')==='1' ? 'selected':'' }}>Disponible</option>
                    <option value="0" {{ request('disponible')==='0' ? 'selected':'' }}>Indisponible</option>
                </select>
            </div>
            <button type="submit" class="btn-indigo btn btn-sm">Filtrer</button>
        </form>

        <div class="card-tissu" style="padding:0;overflow:hidden;">
            <table class="table table-tissu mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Spécialité</th>
                        <th>Expérience</th>
                        <th>Disponible</th>
                        <th>Formations</th>
                        <th style="width:200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formateurs as $f)
                    <tr>
                        <td>
                            @if($f->est_externe)
                                <div style="font-weight:600;">
                                    {{ $f->user?->nom_complet ?? '—' }}
                                </div>
                                @if($f->organisme)
                                    <div style="font-size:12px;color:var(--gris-doux);">{{ $f->organisme }}</div>
                                @endif
                                @if(!$f->user_id)
                                    <span style="font-size:11px;color:var(--gris-doux);">Sans accès login</span>
                                @endif
                            @else
                                <div style="font-weight:600;">{{ $f->artisan?->user?->nom_complet ?? '—' }}</div>
                                <div style="font-size:12px;color:var(--gris-doux);">Artisan interne</div>
                            @endif
                        </td>
                        <td>
                            @if($f->est_externe)
                                <span style="font-size:11px;background:#dbeafe;color:#1e40af;border-radius:20px;padding:2px 8px;">Externe</span>
                            @else
                                <span style="font-size:11px;background:var(--sable);color:var(--texte);border-radius:20px;padding:2px 8px;">Interne</span>
                            @endif
                        </td>
                        <td>{{ $f->specialite }}</td>
                        <td style="color:var(--gris-doux);">
                            {{ $f->experience_annees ? $f->experience_annees . ' ans' : '—' }}
                        </td>
                        <td>
                            @if($f->is_disponible)
                                <span class="badge-statut badge-confirmed">Oui</span>
                            @else
                                <span class="badge-statut badge-cancelled">Non</span>
                            @endif
                        </td>
                        <td>{{ $f->formations_count }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <form method="POST" action="{{ route('admin.formateurs.disponible', $f->id) }}">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-outline-or" title="Basculer disponibilité">
                                        <i class="bi bi-toggle-{{ $f->is_disponible ? 'on' : 'off' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.formateurs.edit', $f->id) }}" class="btn btn-sm btn-outline-or">Modifier</a>
                                <form method="POST" action="{{ route('admin.formateurs.destroy', $f->id) }}"
                                      onsubmit="return confirm('Supprimer ce formateur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="border:1px solid var(--rouge-fes);color:var(--rouge-fes);background:transparent;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--gris-doux);">
                            Aucun formateur trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $formateurs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
