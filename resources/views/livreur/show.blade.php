@extends('layouts.app')
@section('title', 'Livraison #'.$livraison->commande_id)

@push('styles')
<style>
.liv-detail { background: var(--sable); padding: 32px 0 64px; }
.timeline { position: relative; padding-left: 28px; }
.timeline::before {
  content: ''; position: absolute; left: 8px; top: 4px; bottom: 4px;
  width: 2px; background: var(--sable-dark);
}
.timeline-item { position: relative; padding-bottom: 20px; }
.timeline-item::before {
  content: ''; position: absolute; left: -24px; top: 4px;
  width: 12px; height: 12px; border-radius: 50%; background: var(--or);
  border: 2px solid #fff; box-shadow: 0 0 0 2px var(--or);
}
.panel { background:#fff;border:1px solid var(--sable-dark);border-radius:var(--radius);padding:22px;margin-bottom:16px; }
</style>
@endpush

@section('content')
@php use App\Models\Livraison; @endphp

@include('livreur.header')

<div class="liv-detail">
  <div class="container-xl">

    <a href="{{ route('livreur.dashboard') }}" class="d-inline-flex align-items-center gap-2 mb-3" style="color:var(--or-dark);text-decoration:none;">
      <i class="bi bi-arrow-left"></i> Retour aux livraisons
    </a>

    @if(session('success'))
      <div class="alert-tissu success mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert-tissu error mb-3">{{ session('error') }}</div>
    @endif

    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
      <h1 style="font-family:'Amiri',serif;font-size:26px;margin:0;">Livraison #{{ $livraison->commande_id }}</h1>
      <span class="badge-statut badge-confirmed" style="font-size:14px;padding:6px 14px;">
        {{ Livraison::statutLabel($livraison->statut) }}
      </span>
    </div>

    <div class="row g-4">
      {{-- Commande --}}
      <div class="col-lg-7">
        <div class="panel">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">Détails commande</h3>
          @php $client = $livraison->commande?->client; @endphp
          <p><strong>Client :</strong> {{ $client?->nom_complet ?? '—' }}</p>
          @if($client?->telephone)
            <p><strong>Téléphone :</strong> <a href="tel:{{ $client->telephone }}">{{ $client->telephone }}</a></p>
          @endif
          @if($client?->email)
            <p><strong>Email :</strong> {{ $client->email }}</p>
          @endif
          <p><strong>Adresse :</strong> {{ $livraison->adresse_livraison ?? $livraison->commande?->adresse_livraison ?? '—' }}</p>
          <p><strong>Ville :</strong> {{ $livraison->ville ?? $livraison->commande?->ville ?? '—' }}</p>

          <hr style="border-color:var(--sable-dark);">
          <h4 style="font-size:15px;font-weight:600;margin-bottom:12px;">Articles</h4>
          @forelse($livraison->commande?->lignes ?? [] as $ligne)
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--sable-dark)!important;font-size:14px;">
              <span>{{ $ligne->produit?->nom ?? 'Produit' }} × {{ $ligne->quantite }}</span>
              <span>{{ number_format($ligne->sous_total, 0) }} MAD</span>
            </div>
          @empty
            <p class="text-muted small">Aucun article.</p>
          @endforelse
          <div class="d-flex justify-content-between mt-3 fw-bold">
            <span>Total TTC</span>
            <span style="color:var(--or-dark);">{{ number_format($livraison->commande?->total_ttc ?? 0, 0) }} MAD</span>
          </div>
        </div>
      </div>

      {{-- Livraison + actions --}}
      <div class="col-lg-5">
        <div class="panel">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">Informations livraison</h3>
          @if($livraison->transporteur)
            <p><strong>Transporteur :</strong> {{ $livraison->transporteur }}</p>
          @endif
          @if($livraison->numero_suivi)
            <p><strong>N° suivi :</strong> {{ $livraison->numero_suivi }}</p>
          @endif
          @if($livraison->date_livraison_prevue)
            <p><strong>Prévue le :</strong> {{ $livraison->date_livraison_prevue->format('d/m/Y') }}</p>
          @endif
          @if($livraison->date_livraison_reelle)
            <p><strong>Livrée le :</strong> {{ $livraison->date_livraison_reelle->format('d/m/Y H:i') }}</p>
          @endif
          @if($livraison->preuve_livraison_url)
            <p class="mb-2"><strong>Preuve :</strong></p>
            <img src="{{ $livraison->preuve_livraison_url }}" alt="Preuve" style="max-width:100%;border-radius:8px;border:1px solid var(--sable-dark);">
          @endif

          <div class="mt-3 d-flex flex-column gap-2">
            @if($livraison->statut === Livraison::STATUT_ASSIGNEE)
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <form method="POST" action="{{ route('livreur.livraisons.accepter', $livraison->id) }}" style="flex:1;"
                      onsubmit="return confirm('Accepter cette livraison ?');">
                  @csrf @method('PUT')
                  <button type="submit" class="btn-or btn w-100" style="min-height:44px;">Accepter</button>
                </form>
                <button type="button"
                        style="flex:1;padding:11px;font-size:14px;background:none;
                               border:1.5px solid var(--rouge-fes);color:var(--rouge-fes);
                               border-radius:var(--radius-sm);cursor:pointer;min-height:44px;"
                        onclick="document.getElementById('refuse-modal-{{ $livraison->id }}').style.display='flex'">
                  Refuser
                </button>
              </div>
              <div id="refuse-modal-{{ $livraison->id }}"
                   style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);
                          z-index:1000;align-items:center;justify-content:center;padding:20px;">
                <div style="background:white;border-radius:var(--radius);padding:24px;max-width:400px;width:100%;">
                  <h4 style="font-family:'Amiri',serif;margin-bottom:14px;">Refuser la livraison #{{ $livraison->commande_id }}</h4>
                  <form method="POST" action="{{ route('livreur.livraisons.refuser', $livraison->id) }}">
                    @csrf @method('PUT')
                    <label class="form-label-tissu">Motif (optionnel)</label>
                    <textarea name="motif" class="form-control-tissu" rows="3" style="margin-bottom:14px;"></textarea>
                    <div style="display:flex;gap:8px;">
                      <button type="button" onclick="document.getElementById('refuse-modal-{{ $livraison->id }}').style.display='none'"
                              style="flex:1;padding:10px;background:var(--sable);border:none;border-radius:var(--radius-sm);cursor:pointer;">Annuler</button>
                      <button type="submit" style="flex:1;padding:10px;background:var(--rouge-fes);color:white;border:none;border-radius:var(--radius-sm);cursor:pointer;">Confirmer le refus</button>
                    </div>
                  </form>
                </div>
              </div>
            @elseif($livraison->statut === Livraison::STATUT_EN_TRANSIT)
              <form method="POST" action="{{ route('livreur.livraisons.statut', $livraison->id) }}" class="d-flex flex-column gap-2">
                @csrf @method('PUT')
                <select name="statut" class="form-control-tissu">
                  <option value="{{ Livraison::STATUT_EN_TRANSIT }}" selected>En route</option>
                  <option value="{{ Livraison::STATUT_ECHOUEE }}">Retournée</option>
                </select>
                <input type="text" name="commentaire" class="form-control-tissu" placeholder="Commentaire (optionnel)">
                <button type="submit" class="btn-outline-or btn w-100" style="min-height:44px;">Mettre à jour le statut</button>
              </form>
              <form method="POST" action="{{ route('livreur.livraisons.confirmer', $livraison->id) }}" enctype="multipart/form-data"
                    onsubmit="return confirm('Confirmer que ce colis a été livré ? Cette action est définitive.');">
                @csrf
                <textarea name="commentaire" class="form-control-tissu mb-2" rows="2" placeholder="Commentaire (optionnel)"></textarea>
                <input type="file" name="preuve" accept="image/*" class="form-control-tissu mb-2">
                <button type="submit" class="btn w-100" style="background:var(--vert-atlas);color:#fff;min-height:44px;">✅ Confirmer livraison</button>
              </form>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Historique --}}
    <div class="panel mt-2">
      <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">Historique</h3>
      @if($livraison->historique->isEmpty())
        <p class="text-muted small mb-0">Aucun historique.</p>
      @else
        <div class="timeline">
          @foreach($livraison->historique as $h)
            <div class="timeline-item">
              <div style="font-weight:600;">{{ Livraison::statutLabel($h->statut) }}</div>
              @if($h->commentaire)
                <div style="font-size:14px;">{{ $h->commentaire }}</div>
              @endif
              <div style="font-size:12px;color:var(--gris-doux);">
                {{ $h->modifiePar?->nom_complet ?? 'Système' }}
                · {{ $h->created_at?->diffForHumans() }}
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>
</div>
@endsection
