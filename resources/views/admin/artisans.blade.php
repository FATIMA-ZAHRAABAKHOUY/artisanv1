@extends('layouts.admin')
@section('title', 'Artisans')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Artisans</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="admin-page-title">Artisans</h1>
    <p class="text-muted small mb-0">Gestion et validation des artisans de la coopérative</p>
</div>

<div class="admin-tabs">
    <a href="{{ route('admin.artisans') }}" class="{{ !request('filtre') ? 'active' : '' }}">
        Tous ({{ \App\Models\Artisan::count() }})
    </a>
    <a href="{{ route('admin.artisans', ['filtre'=>'en_attente']) }}" class="danger {{ request('filtre')=='en_attente' ? 'active danger' : '' }}">
        En attente ({{ \App\Models\Artisan::where('is_verified',false)->count() }})
    </a>
</div>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr>
                <th>Artisan</th><th>Spécialité</th><th>Statut</th><th>Vérifié</th>
                <th>Produits</th><th>Note</th><th>Adhésion</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($artisans as $a)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle">{{ substr($a->user?->prenom ?? 'A', 0, 1) }}</div>
                        <div>
                            <div class="fw-semibold small">{{ $a->user?->nom_complet }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $a->user?->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="small">{{ $a->specialite }}</td>
                <td><span class="badge-statut-sm badge-{{ $a->statut === 'actif' ? 'actif' : 'suspendu' }}">{{ $a->statut }}</span></td>
                <td>{!! $a->is_verified ? '<i class="fa-solid fa-circle-check text-success"></i>' : '<i class="fa-solid fa-clock text-warning"></i>' !!}</td>
                <td class="text-center fw-semibold">{{ $a->produits_count ?? 0 }}</td>
                <td class="text-warning small"><i class="fa-solid fa-star"></i> {{ number_format($a->note_moyenne, 1) }}</td>
                <td class="text-muted small">{{ $a->date_adhesion?->format('d/m/Y') }}</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        @if(!$a->is_verified)
                        <form method="POST" action="{{ route('admin.artisans.valider', $a->id) }}">@csrf
                            <button type="submit" class="btn btn-admin-primary btn-admin-sm">Valider</button>
                        </form>
                        @endif
                        @if($a->statut === 'actif')
                        <form method="POST" action="{{ route('admin.artisans.suspendre', $a->id) }}">@csrf
                            <button type="submit" class="btn btn-outline-danger-sm btn-admin-sm">Suspendre</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-5">Aucun artisan trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $artisans->links() }}</div>
@endsection
