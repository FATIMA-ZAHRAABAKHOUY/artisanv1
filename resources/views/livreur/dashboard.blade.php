@extends('layouts.app')
@section('title', 'Espace Livreur — Tissu Artisanal')

@push('styles')
<style>
.livreur-page { background: var(--sable); padding: 32px 0 64px; min-height: calc(100vh - 120px); }
.livreur-kpi {
  background: #fff; border: 1px solid var(--sable-dark); border-radius: var(--radius);
  padding: 18px 16px; box-shadow: var(--shadow-sm); height: 100%;
  border-top: 3px solid var(--or);
}
.livreur-kpi.indigo { border-top-color: var(--indigo); }
.livreur-kpi.vert { border-top-color: var(--vert-atlas); }
.livreur-kpi.rouge { border-top-color: var(--rouge-fes); }
.livreur-kpi .val { font-family: 'Amiri', serif; font-size: 28px; font-weight: 700; line-height: 1; color: var(--texte); }
.livreur-kpi .lbl { font-size: 13px; color: var(--gris-doux); margin-top: 6px; }
.filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.filter-tab {
  padding: 8px 14px; border-radius: 20px; font-size: 13px; font-weight: 500;
  text-decoration: none; color: var(--texte); background: #fff;
  border: 1px solid var(--sable-dark); transition: var(--transition);
}
.filter-tab.active, .filter-tab:hover { background: var(--or); color: #fff; border-color: var(--or); }
.liv-card {
  background: #fff; border: 1px solid var(--sable-dark); border-radius: var(--radius);
  padding: 18px; margin-bottom: 14px; box-shadow: var(--shadow-sm);
}
.liv-card-top { display: flex; gap: 14px; align-items: flex-start; flex-wrap: wrap; }
.liv-icon {
  width: 48px; height: 48px; border-radius: 50%; display: flex;
  align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
}
.liv-actions { margin-top: 14px; display: flex; flex-direction: column; gap: 10px; }
.liv-actions .btn { min-height: 44px; }
.confirm-box {
  background: var(--sable); border-radius: var(--radius-sm);
  padding: 14px; margin-top: 10px; display: none;
}
.confirm-box.open { display: block; }
</style>
@endpush

@section('content')
@php
  use App\Models\Livraison;
  $filtre = request('statut');
  $statusStyles = [
    Livraison::STATUT_ASSIGNEE   => ['icon' => '📦', 'bg' => '#fef3c7', 'badge' => 'badge-pending'],
    Livraison::STATUT_EN_TRANSIT => ['icon' => '🚚', 'bg' => '#ede9fe', 'badge' => 'badge-processing'],
    Livraison::STATUT_LIVREE     => ['icon' => '✅', 'bg' => '#d1fae5', 'badge' => 'badge-delivered'],
    Livraison::STATUT_ECHOUEE    => ['icon' => '↩️', 'bg' => '#fee2e2', 'badge' => 'badge-cancelled'],
  ];
@endphp

@include('livreur.header')

<div class="livreur-page">
  <div class="container-xl">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-truck"></i></div>
        <div>
          <h2>Bonjour, {{ auth()->user()->prenom }} 🚚</h2>
          <p>Gérez vos livraisons assignées</p>
        </div>
      </div>
      @include('partials.date-theme-widget')
    </div>

    @if(session('success'))
      <div class="alert-tissu success mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert-tissu error mb-3"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- KPI --}}
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="livreur-kpi"><div class="val">{{ $stats['a_preparer'] }}</div><div class="lbl">À préparer</div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="livreur-kpi indigo"><div class="val">{{ $stats['en_route'] }}</div><div class="lbl">En route</div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="livreur-kpi vert"><div class="val">{{ $stats['livrees'] }}</div><div class="lbl">Livrées</div></div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="livreur-kpi rouge"><div class="val">{{ $stats['retournees'] }}</div><div class="lbl">Retournées</div></div>
      </div>
    </div>

    {{-- Filtres --}}
    <div class="filter-tabs">
      <a href="{{ route('livreur.dashboard') }}" class="filter-tab {{ !$filtre ? 'active' : '' }}">Toutes actives</a>
      <a href="{{ route('livreur.dashboard', ['statut' => Livraison::STATUT_ASSIGNEE]) }}" class="filter-tab {{ $filtre === Livraison::STATUT_ASSIGNEE ? 'active' : '' }}">À préparer</a>
      <a href="{{ route('livreur.dashboard', ['statut' => 'en_route']) }}" class="filter-tab {{ $filtre === 'en_route' ? 'active' : '' }}">En route</a>
      <a href="{{ route('livreur.dashboard', ['statut' => Livraison::STATUT_LIVREE]) }}" class="filter-tab {{ $filtre === Livraison::STATUT_LIVREE ? 'active' : '' }}">Livrées</a>
    </div>

    {{-- Pool : livraisons non assignées --}}
    <div style="margin-top:24px;margin-bottom:28px;">
      <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-broadcast" style="color:var(--vert-atlas);"></i>
        Livraisons disponibles
        @if(($stats['disponibles_count'] ?? 0) > 0)
          <span style="background:var(--vert-atlas);color:white;border-radius:20px;padding:2px 10px;font-size:12px;">
            {{ $stats['disponibles_count'] }}
          </span>
        @endif
      </h3>

      @forelse($disponibles ?? [] as $d)
        <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                    padding:16px 20px;margin-bottom:10px;display:flex;align-items:center;
                    justify-content:space-between;gap:14px;flex-wrap:wrap;">
          <div>
            <div style="font-weight:600;font-size:14px;">Commande #{{ $d->commande_id }}</div>
            <div style="font-size:13px;color:var(--gris-doux);">
              {{ $d->commande?->client?->nom_complet ?? '—' }} — {{ $d->ville ?? $d->commande?->ville ?? '—' }}
            </div>
            <div style="font-size:12px;color:var(--gris-doux);margin-top:3px;">
              <i class="bi bi-geo-alt"></i> {{ $d->adresse_livraison ?? '—' }}
            </div>
          </div>
          <form method="POST" action="{{ route('livreur.livraisons.claim', $d->id) }}">
            @csrf @method('PUT')
            <button type="submit" class="btn-or" style="padding:10px 20px;font-size:13px;min-height:44px;"
                    onclick="return confirm('Prendre en charge cette livraison ?')">
              <i class="bi bi-hand-index-thumb me-2"></i>Prendre en charge
            </button>
          </form>
        </div>
      @empty
        <div style="text-align:center;padding:30px;color:var(--gris-doux);background:var(--sable);border-radius:var(--radius);">
          <i class="bi bi-inbox" style="font-size:32px;"></i>
          <p style="margin-top:8px;font-size:13px;margin-bottom:0;">Aucune livraison disponible pour le moment.</p>
        </div>
      @endforelse
    </div>

    {{-- Liste --}}
    @forelse($livraisons as $l)
      @php $style = $statusStyles[$l->statut] ?? ['icon' => '📦', 'bg' => '#f3f4f6', 'badge' => '']; @endphp
      <div class="liv-card">
        <div class="liv-card-top">
          <div class="liv-icon" style="background:{{ $style['bg'] }};">{{ $style['icon'] }}</div>
          <div class="flex-grow-1" style="min-width:200px;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <strong>Commande #{{ $l->commande_id }}</strong>
              <span class="badge-statut {{ $style['badge'] }}">{{ Livraison::statutLabel($l->statut) }}</span>
            </div>
            <div style="font-size:14px;margin-top:8px;">
              <i class="bi bi-person me-1"></i>
              {{ $l->commande?->client?->nom_complet ?? '—' }}
              @if($l->commande?->client?->telephone)
                · <a href="tel:{{ $l->commande->client->telephone }}" style="color:var(--or-dark);font-weight:600;">
                  {{ $l->commande->client->telephone }}
                </a>
              @endif
            </div>
            <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">
              <i class="bi bi-geo-alt me-1"></i>
              {{ $l->adresse_livraison ?? '—' }}{{ $l->ville ? ', '.$l->ville : '' }}
            </div>
            @if($l->numero_suivi)
              <div style="font-size:12px;margin-top:4px;">Suivi : <strong>{{ $l->numero_suivi }}</strong></div>
            @endif
            @if($l->date_livraison_prevue)
              <div style="font-size:12px;color:var(--gris-doux);margin-top:4px;">
                Prévue le {{ $l->date_livraison_prevue->format('d/m/Y') }}
              </div>
            @endif
          </div>
        </div>

        <div class="liv-actions">
          @if($l->statut === Livraison::STATUT_ASSIGNEE)
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <form method="POST" action="{{ route('livreur.livraisons.accepter', $l->id) }}" style="flex:1;">
                @csrf @method('PUT')
                <button type="submit" class="btn-or" style="width:100%;padding:11px;font-size:14px;min-height:44px;"
                        onclick="return confirm('Accepter cette livraison ?')">
                  <i class="bi bi-check-circle me-2"></i>Accepter
                </button>
              </form>

              <button type="button"
                      style="flex:1;padding:11px;font-size:14px;background:none;
                             border:1.5px solid var(--rouge-fes);color:var(--rouge-fes);
                             border-radius:var(--radius-sm);cursor:pointer;min-height:44px;"
                      onclick="document.getElementById('refuse-modal-{{ $l->id }}').style.display='flex'">
                <i class="bi bi-x-circle me-2"></i>Refuser
              </button>
            </div>

            <div id="refuse-modal-{{ $l->id }}"
                 style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);
                        z-index:1000;align-items:center;justify-content:center;padding:20px;">
              <div style="background:white;border-radius:var(--radius);padding:24px;max-width:400px;width:100%;">
                <h4 style="font-family:'Amiri',serif;margin-bottom:14px;">
                  Refuser la livraison #{{ $l->commande_id }}
                </h4>
                <form method="POST" action="{{ route('livreur.livraisons.refuser', $l->id) }}">
                  @csrf @method('PUT')
                  <label class="form-label-tissu">Motif (optionnel)</label>
                  <textarea name="motif" class="form-control-tissu" rows="3"
                            placeholder="Ex: zone trop éloignée, indisponible..."
                            style="margin-bottom:14px;"></textarea>
                  <div style="display:flex;gap:8px;">
                    <button type="button"
                            onclick="document.getElementById('refuse-modal-{{ $l->id }}').style.display='none'"
                            style="flex:1;padding:10px;background:var(--sable);border:none;
                                   border-radius:var(--radius-sm);cursor:pointer;">
                      Annuler
                    </button>
                    <button type="submit"
                            style="flex:1;padding:10px;background:var(--rouge-fes);color:white;
                                   border:none;border-radius:var(--radius-sm);cursor:pointer;">
                      Confirmer le refus
                    </button>
                  </div>
                </form>
              </div>
            </div>
          @elseif($l->statut === Livraison::STATUT_EN_TRANSIT)
            <form method="POST" action="{{ route('livreur.livraisons.statut', $l->id) }}" class="d-flex flex-wrap gap-2">
              @csrf @method('PUT')
              <select name="statut" class="form-control-tissu" style="flex:1;min-width:140px;">
                <option value="{{ Livraison::STATUT_EN_TRANSIT }}" selected>En route</option>
                <option value="{{ Livraison::STATUT_ECHOUEE }}">Retournée</option>
              </select>
              <input type="text" name="commentaire" class="form-control-tissu" placeholder="Commentaire (opt.)" style="flex:2;min-width:160px;">
              <button type="submit" class="btn-outline-or btn">Mettre à jour</button>
            </form>
            <button type="button" class="btn w-100" style="background:var(--vert-atlas);color:#fff;min-height:44px;"
                    onclick="toggleConfirm({{ $l->id }})">
              ✅ Confirmer livraison
            </button>
            <div id="confirm-{{ $l->id }}" class="confirm-box">
              <form method="POST" action="{{ route('livreur.livraisons.confirmer', $l->id) }}"
                    enctype="multipart/form-data"
                    onsubmit="return confirm('Confirmer que ce colis a été livré ? Cette action est définitive.');">
                @csrf
                <textarea name="commentaire" class="form-control-tissu mb-2" rows="2" placeholder="Commentaire (optionnel)"></textarea>
                <input type="file" name="preuve" accept="image/*" class="form-control-tissu mb-2">
                <button type="submit" class="btn w-100" style="background:var(--vert-atlas);color:#fff;">Valider la livraison</button>
              </form>
            </div>
          @else
            @if($l->statut === Livraison::STATUT_LIVREE && $l->date_livraison_reelle)
              <div class="text-muted small">Livrée le {{ $l->date_livraison_reelle->format('d/m/Y H:i') }}</div>
            @endif
            <a href="{{ route('livreur.livraisons.show', $l->id) }}" class="btn-outline-or btn w-100">Voir détails</a>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center py-5">
        <div style="font-size:56px;margin-bottom:12px;">🚚</div>
        <p class="text-muted mb-0">Aucune livraison pour le moment.</p>
      </div>
    @endforelse

    <div class="mt-3">{{ $livraisons->links() }}</div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function toggleConfirm(id) {
  document.querySelectorAll('.confirm-box').forEach(el => el.classList.remove('open'));
  document.getElementById('confirm-' + id)?.classList.add('open');
}
</script>
@endpush
