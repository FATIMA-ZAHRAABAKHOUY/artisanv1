@extends('layouts.admin')
@section('title', 'Utilisateurs')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Utilisateurs</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="admin-page-title">Utilisateurs</h1>
        <p class="text-muted small mb-0">{{ $users->total() }} utilisateur(s) enregistré(s)</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.users') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
    <div>
        <label>Recherche</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, email…" style="width:200px;">
    </div>
    <div>
        <label>Rôle</label>
        <select name="role" class="form-select" style="width:140px;">
            <option value="">Tous</option>
            @foreach(['client','artisan','admin','livreur','apprenant'] as $r)
                <option value="{{ $r }}" {{ request('role')==$r ? 'selected':'' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Statut</label>
        <select name="statut" class="form-select" style="width:130px;">
            <option value="">Tous</option>
            @foreach(['actif','inactif','suspendu'] as $s)
                <option value="{{ $s }}" {{ request('statut')==$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-admin-primary btn-admin-sm">Filtrer</button>
    <a href="{{ route('admin.users') }}" class="text-muted small">Réinitialiser</a>
</form>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr>
                <th>Utilisateur</th><th>Rôle</th><th>Statut</th><th>Ville</th><th>Inscrit le</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php $roleColors = ['client'=>'badge-confirmed','artisan'=>'badge-verified','admin'=>'badge-shipped','livreur'=>'badge-processing','apprenant'=>'badge-pending']; @endphp
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle">{{ substr($user->prenom,0,1) }}</div>
                        <div>
                            <div class="fw-semibold small">{{ $user->nom_complet }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="badge-role {{ $roleColors[$user->role] ?? '' }}">{{ $user->role }}</span></td>
                <td><span class="badge-statut-sm badge-{{ $user->statut === 'actif' ? 'actif-user' : $user->statut }}">{{ $user->statut }}</span></td>
                <td class="text-muted small">{{ $user->ville ?? '—' }}</td>
                <td class="text-muted small">{{ $user->created_at?->format('d/m/Y') }}</td>
                <td>
                    @if($user->statut === 'actif')
                    <form method="POST" action="{{ route('admin.users.suspendre', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="btn btn-outline-danger-sm btn-admin-sm">Suspendre</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.users.activer', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="btn btn-outline-success-sm btn-admin-sm">Activer</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Aucun utilisateur trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
