@extends('layouts.app')
@section('title', "Mes commandes — L'Âme du Fil")

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('artisan.dashboard') }}">Espace Artisan</a></li>
  <li class="breadcrumb-item active">Mes commandes</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-bag-check"></i></div>
      <div>
        <h2>Mes commandes</h2>
        <p>Commandes contenant vos produits</p>
      </div>
    </div>

    <form method="GET" action="{{ route('artisan.commandes') }}" class="d-flex flex-wrap gap-3 align-items-end mb-4">
      <div>
        <label class="form-label-tissu">Statut</label>
        <select name="statut" class="form-select-tissu" style="width:180px;">
          <option value="">Tous</option>
          @foreach(['pending'=>'En attente','confirmed'=>'Confirmées','processing'=>'En préparation','shipped'=>'Expédiées','delivered'=>'Livrées','cancelled'=>'Annulées'] as $val => $label)
            <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn-outline-or">Filtrer</button>
    </form>

    <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);overflow:hidden;">
      <table class="table-tissu mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Ville</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($commandes as $cmd)
            @php $bMap = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','processing'=>'badge-processing','shipped'=>'badge-shipped','delivered'=>'badge-delivered','cancelled'=>'badge-cancelled']; @endphp
            <tr>
              <td class="fw-bold">#{{ $cmd->id }}</td>
              <td>
                <div class="fw-semibold">{{ $cmd->client }}</div>
                <div class="text-muted small">{{ $cmd->client_tel ?? '—' }}</div>
              </td>
              <td>{{ $cmd->ville ?? '—' }}</td>
              <td class="fw-bold" style="color:var(--or-dark);">{{ number_format($cmd->total_ttc, 0) }} MAD</td>
              <td><span class="badge-statut {{ $bMap[$cmd->statut] ?? '' }}">{{ $cmd->statut }}</span></td>
              <td class="text-muted small">{{ \Carbon\Carbon::parse($cmd->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Aucune commande pour le moment.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $commandes->withQueryString()->links() }}</div>
  </div>
</div>
@endsection
