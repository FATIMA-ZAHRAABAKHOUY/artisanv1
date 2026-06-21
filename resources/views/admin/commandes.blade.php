@extends('layouts.admin')
@section('title', 'Commandes')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Commandes</li>
@endsection

@section('content')
<h1 class="admin-page-title mb-4">Commandes</h1>

<form method="GET" action="{{ route('admin.commandes') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
    <div>
        <label>Statut</label>
        <select name="statut" class="form-select" style="width:150px;">
            <option value="">Tous</option>
            @foreach(['pending'=>'En attente','confirmed'=>'Confirmées','processing'=>'En préparation','shipped'=>'Expédiées','delivered'=>'Livrées','cancelled'=>'Annulées'] as $v => $l)
                <option value="{{ $v }}" {{ request('statut')==$v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Du</label><input type="date" name="date_debut" value="{{ request('date_debut') }}" class="form-control"></div>
    <div><label>Au</label><input type="date" name="date_fin" value="{{ request('date_fin') }}" class="form-control"></div>
    <button type="submit" class="btn btn-admin-primary btn-admin-sm">Filtrer</button>
</form>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr><th>#</th><th>Client</th><th>Total TTC</th><th>Statut</th><th>Paiement</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($commandes as $cmd)
            @php $bMap=['pending'=>'badge-pending','confirmed'=>'badge-confirmed','processing'=>'badge-processing','shipped'=>'badge-shipped','delivered'=>'badge-delivered','cancelled'=>'badge-cancelled']; @endphp
            <tr>
                <td class="fw-bold text-warning">#{{ $cmd->id }}</td>
                <td>
                    <div class="small fw-semibold">{{ $cmd->client?->nom_complet }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ $cmd->ville }}</div>
                </td>
                <td class="fw-bold">{{ number_format($cmd->total_ttc, 0, ',', ' ') }} MAD</td>
                <td><span class="badge-statut-sm {{ $bMap[$cmd->statut] ?? '' }}">{{ $cmd->statut }}</span></td>
                <td>
                    @if($cmd->paiement)
                        <span class="badge-statut-sm {{ $cmd->paiement->estPaye() ? 'badge-delivered' : 'badge-pending' }}">
                            {{ $cmd->paiement->estPaye() ? 'Payé' : $cmd->paiement->methode }}
                        </span>
                    @else — @endif
                </td>
                <td class="text-muted small">{{ $cmd->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap align-items-center">
                        <a href="{{ route('admin.commandes.show', $cmd->id) }}" class="btn btn-sm btn-outline-secondary btn-admin-sm">Détails</a>
                        @if(!in_array($cmd->statut, ['delivered','cancelled']))
                        <form method="POST" action="{{ route('admin.commandes.statut', $cmd->id) }}" class="d-flex gap-1">@csrf
                            <select name="statut" class="form-select form-select-sm" style="width:auto;">
                                @foreach(['confirmed','processing','shipped','delivered','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $cmd->statut==$s ? 'selected':'' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-admin-primary btn-admin-sm">OK</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Aucune commande</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $commandes->withQueryString()->links() }}</div>
@endsection
