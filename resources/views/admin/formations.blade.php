@extends('layouts.admin')
@section('title', 'Formations')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Formations</li>
@endsection

@section('content')
<h1 class="admin-page-title mb-4">Formations</h1>

<div class="row g-3 mb-4">
    @foreach([
        ['#c8913a', \App\Models\Formation::where('is_active',true)->count(), 'Formations actives'],
        ['#3b82f6', \App\Models\InscriptionFormation::whereIn('statut',['inscrit','confirme'])->count(), 'Inscriptions actives'],
        ['#22c55e', \App\Models\InscriptionFormation::where('statut_inscription','terminee')->count(), 'Terminées'],
        ['#ef4444', \App\Models\InscriptionFormation::where('statut_inscription','abandonnee')->count(), 'Abandonnées'],
    ] as [$color, $val, $lbl])
    <div class="col-6 col-xl-3">
        <div class="mini-stat" style="--accent: {{ $color }}"><div class="val">{{ $val }}</div><div class="lbl">{{ $lbl }}</div></div>
    </div>
    @endforeach
</div>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr><th>Formation</th><th>Artisan</th><th>Dates</th><th>Prix</th><th>Places</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($formations as $f)
            @php
                $inscrits = $f->inscriptions()->whereIn('statut',['inscrit','confirme'])->count();
                $pct = $f->places_max > 0 ? min(100, ($inscrits/$f->places_max)*100) : 0;
            @endphp
            <tr>
                <td>
                    <div class="small fw-semibold">{{ str($f->titre)->limit(40) }}</div>
                    <div class="text-muted" style="font-size:.75rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ str($f->lieu)->limit(30) }}</div>
                </td>
                <td class="small">{{ $f->artisan?->user?->nom_complet }}</td>
                <td class="text-muted small">{{ $f->date_debut?->format('d/m/Y') }}<br>{{ $f->date_fin?->format('d/m/Y') }}</td>
                <td class="fw-bold text-warning">{{ $f->prix == 0 ? 'Gratuit' : number_format($f->prix,0,',',' ').' MAD' }}</td>
                <td>
                    <div class="small mb-1">{{ $inscrits }}/{{ $f->places_max }}</div>
                    <div class="progress-thin"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                </td>
                <td><span class="badge-statut-sm {{ $f->is_active ? 'badge-actif' : 'badge-inactif' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td><a href="{{ route('formations.show', $f->id) }}" class="btn btn-sm btn-outline-secondary btn-admin-sm">Voir</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Aucune formation</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $formations->links() }}</div>
@endsection
