@extends('layouts.admin')
@section('title', 'Livraisons')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Livraisons</li>
@endsection

@section('content')
<h1 class="admin-page-title mb-4">Livraisons</h1>

<form method="GET" action="{{ route('admin.livraisons') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
    <div>
        <label>Statut</label>
        <select name="statut" class="form-select" style="width:160px;">
            <option value="">Tous</option>
            @foreach(['assigned'=>'Assignée','in_transit'=>'En transit','delivered'=>'Livrée','failed'=>'Échouée'] as $v=>$l)
                <option value="{{ $v }}" {{ request('statut')==$v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Sans livreur</label>
        <select name="sans_livreur" class="form-select" style="width:130px;">
            <option value="">Tous</option>
            <option value="1" {{ request('sans_livreur')=='1' ? 'selected':'' }}>À assigner</option>
        </select>
    </div>
    <button type="submit" class="btn btn-admin-primary btn-admin-sm">Filtrer</button>
</form>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr><th>Commande</th><th>Client</th><th>Adresse</th><th>Statut</th><th>Livreur</th><th>Prévu le</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($livraisons as $l)
            @php $sMap=['assigned'=>'badge-pending','in_transit'=>'badge-processing','delivered'=>'badge-delivered','failed'=>'badge-cancelled']; @endphp
            <tr>
                <td class="fw-bold text-warning">#{{ $l->commande_id }}</td>
                <td class="small">{{ $l->commande?->client?->nom_complet }}</td>
                <td class="text-muted small">{{ str($l->adresse ?? $l->ville ?? '—')->limit(30) }}</td>
                <td><span class="badge-statut-sm {{ $sMap[$l->statut] ?? '' }}">{{ $l->statut }}</span></td>
                <td class="small">{{ $l->livreur?->nom_complet ?? '—' }}</td>
                <td class="text-muted small">{{ $l->date_livraison_prev?->format('d/m/Y') ?? '—' }}</td>
                <td>
                    @if(!$l->livreur_id)
                        <a href="{{ route('admin.livraisons.assigner', $l->id) }}" class="btn btn-admin-primary btn-admin-sm">Assigner</a>
                    @else
                        <a href="{{ route('admin.commandes.show', $l->commande_id) }}" class="btn btn-sm btn-outline-secondary btn-admin-sm">Détails</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Aucune livraison</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $livraisons->withQueryString()->links() }}</div>
@endsection
