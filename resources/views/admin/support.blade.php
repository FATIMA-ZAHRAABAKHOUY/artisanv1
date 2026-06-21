@extends('layouts.admin')
@section('title', 'Support')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Support</li>
@endsection

@section('content')
<h1 class="admin-page-title mb-4">Tickets Support</h1>

<form method="GET" action="{{ route('admin.support') }}" class="admin-filter d-flex flex-wrap gap-3 align-items-end">
    <div>
        <label>Statut</label>
        <select name="statut" class="form-select" style="width:160px;">
            <option value="">Tous</option>
            @foreach(['ouvert'=>'Ouverts','en_cours'=>'En cours','resolu'=>'Résolus','ferme'=>'Fermés'] as $v=>$l)
                <option value="{{ $v }}" {{ request('statut')==$v ? 'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-admin-primary btn-admin-sm">Filtrer</button>
</form>

<div class="row g-3 mb-4">
    @foreach([
        ['#ef4444', \App\Models\Support::where('statut','ouvert')->count(), 'Ouverts'],
        ['#f59e0b', \App\Models\Support::where('statut','en_cours')->count(), 'En cours'],
        ['#22c55e', \App\Models\Support::where('statut','resolu')->count(), 'Résolus'],
        ['#64748b', \App\Models\Support::where('statut','ferme')->count(), 'Fermés'],
    ] as [$color, $val, $lbl])
    <div class="col-6 col-xl-3">
        <div class="mini-stat" style="--accent: {{ $color }}"><div class="val">{{ $val }}</div><div class="lbl">{{ $lbl }}</div></div>
    </div>
    @endforeach
</div>

<div class="admin-table-wrap">
    <table class="table table-dash table-hover mb-0">
        <thead>
            <tr><th>#</th><th>Client</th><th>Objet</th><th>Livraison</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
            @php $tColors=['ouvert'=>'badge-cancelled','en_cours'=>'badge-processing','resolu'=>'badge-delivered','ferme'=>'badge-inactif']; @endphp
            <tr>
                <td class="fw-bold text-warning">#{{ $ticket->id }}</td>
                <td>
                    <div class="small fw-semibold">{{ $ticket->user?->nom_complet }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ $ticket->user?->email }}</div>
                </td>
                <td>
                    <div class="small fw-semibold">{{ str($ticket->objet)->limit(35) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">{{ str($ticket->description)->limit(50) }}</div>
                </td>
                <td class="text-muted small">{{ $ticket->livraison ? '#'.$ticket->livraison->id : '—' }}</td>
                <td><span class="badge-statut-sm {{ $tColors[$ticket->statut] ?? '' }}">{{ $ticket->statut }}</span></td>
                <td class="text-muted small">{{ $ticket->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.support.statut', $ticket->id) }}" class="d-flex gap-1">@csrf @method('PUT')
                        <select name="statut" class="form-select form-select-sm" style="width:auto;">
                            @foreach(['ouvert','en_cours','resolu','ferme'] as $s)
                                <option value="{{ $s }}" {{ $ticket->statut==$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-admin-primary btn-admin-sm">OK</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Aucun ticket</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-4">{{ $tickets->withQueryString()->links() }}</div>
@endsection
