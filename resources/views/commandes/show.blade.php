@extends('layouts.app')
@section('title', 'Commande #'.$commande->id)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('commandes.index') }}">Mes commandes</a></li>
  <li class="breadcrumb-item active">Commande #{{ $commande->id }}</li>
@endsection

@push('styles')
<style>
.tracking-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 24px 0; position: relative;
}
.tracking-bar::before {
    content: ''; position: absolute; top: 50%; left: 0; right: 0;
    height: 3px; background: var(--sable-dark); transform: translateY(-50%); z-index: 0;
}
.tracking-step {
    display: flex; flex-direction: column; align-items: center;
    position: relative; z-index: 1; flex: 1;
}
.tracking-dot {
    width: 40px; height: 40px; border-radius: 50%; background: var(--sable-dark);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 8px; border: 3px solid white;
    box-shadow: var(--shadow-sm); transition: all 0.3s;
}
.tracking-dot.done   { background: var(--vert-atlas); color: white; }
.tracking-dot.active { background: var(--or); color: white; }
.tracking-dot.cancel { background: var(--rouge-fes); color: white; }
.tracking-label { font-size: 11px; text-align: center; color: var(--gris-doux); font-weight: 500; }
.tracking-label.active { color: var(--or-dark); font-weight: 700; }
</style>
@endpush

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h1 style="font-family:'Amiri',serif;font-size:28px;margin:0;">
          Commande #{{ $commande->id }}
        </h1>
        <p style="color:var(--gris-doux);margin:4px 0 0;font-size:14px;">
          Passée le {{ $commande->created_at?->format('d/m/Y à H:i') }}
        </p>
      </div>
      @php
        $badges = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed',
                   'processing'=>'badge-processing','shipped'=>'badge-shipped',
                   'delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'];
        $labels = ['pending'=>'⏳ En attente','confirmed'=>'✅ Confirmée',
                   'processing'=>'📦 En préparation','shipped'=>'🚚 Expédiée',
                   'delivered'=>'✔️ Livrée','cancelled'=>'❌ Annulée'];
      @endphp
      <span class="badge-statut {{ $badges[$commande->statut] ?? '' }}" style="font-size:14px;padding:8px 18px;">
        {{ $labels[$commande->statut] ?? $commande->statut }}
      </span>
    </div>

    {{-- Tracking bar --}}
    @if($commande->statut !== 'cancelled')
      <div class="card-tissu p-4 mb-4">
        <div class="tracking-bar">
          @php
            $steps = [
              ['statut'=>'pending',    'icon'=>'🛒', 'label'=>'Commande\nreçue'],
              ['statut'=>'confirmed',  'icon'=>'✅', 'label'=>'Confirmée'],
              ['statut'=>'processing', 'icon'=>'📦', 'label'=>'Préparation'],
              ['statut'=>'shipped',    'icon'=>'🚚', 'label'=>'Expédiée'],
              ['statut'=>'delivered',  'icon'=>'🏠', 'label'=>'Livrée'],
            ];
            $order  = ['pending'=>0,'confirmed'=>1,'processing'=>2,'shipped'=>3,'delivered'=>4];
            $current = $order[$commande->statut] ?? 0;
          @endphp
          @foreach($steps as $i => $step)
            <div class="tracking-step">
              <div class="tracking-dot {{ $i < $current ? 'done' : ($i == $current ? 'active' : '') }}">
                {{ $step['icon'] }}
              </div>
              <div class="tracking-label {{ $i == $current ? 'active' : '' }}">
                {!! str_replace('\n','<br>',$step['label']) !!}
              </div>
            </div>
          @endforeach
        </div>

        {{-- Infos livraison si expédiée --}}
        @if($commande->livraison && in_array($commande->statut,['shipped','delivered']))
          <div style="background:var(--sable);border-radius:var(--radius-sm);padding:14px 18px;
                      display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <i class="bi bi-truck" style="font-size:20px;color:var(--indigo);"></i>
            <div>
              <div style="font-size:13px;font-weight:600;">
                {{ $commande->livraison->transporteur ?? 'Livraison en cours' }}
              </div>
              @if($commande->livraison->numero_suivi)
                <div style="font-size:12.5px;color:var(--gris-doux);">
                  Numéro de suivi : <strong>{{ $commande->livraison->numero_suivi }}</strong>
                </div>
              @endif
            </div>
            @if($commande->livraison->date_livraison_prevue)
              <div style="margin-left:auto;text-align:right;">
                <div style="font-size:11px;color:var(--gris-doux);">Livraison prévue</div>
                <div style="font-size:14px;font-weight:600;color:var(--or-dark);">
                  {{ $commande->livraison->date_livraison_prevue?->format('d/m/Y') }}
                </div>
              </div>
            @endif
          </div>
        @endif
      </div>
    @endif

    <div class="row g-4">
      {{-- Articles --}}
      <div class="col-lg-8">
        <div class="card-tissu p-4 mb-4">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:20px;">
            Articles commandés
          </h3>
          @foreach($commande->lignes as $ligne)
            <div style="display:flex;align-items:center;gap:14px;padding:14px 0;
                        border-bottom:1px solid var(--sable-dark);">
              <div style="width:64px;height:64px;border-radius:8px;background:var(--sable);
                          overflow:hidden;flex-shrink:0;display:flex;align-items:center;
                          justify-content:center;font-size:28px;">
                @if(!empty($ligne->produit?->images[0]))
                  <img src="{{ asset('storage/'.$ligne->produit->images[0]) }}"
                       alt="" style="width:100%;height:100%;object-fit:cover;">
                @else 🧵 @endif
              </div>
              <div style="flex:1;">
                <div style="font-weight:600;font-size:15px;margin-bottom:3px;">
                  {{ $ligne->produit?->nom }}
                </div>
                <div style="font-size:12.5px;color:var(--gris-doux);">
                  <i class="bi bi-person me-1"></i>
                  {{ $ligne->produit?->artisan?->user?->nom_complet }}
                </div>
                <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">
                  {{ number_format($ligne->prix_unitaire, 2) }} MAD × {{ $ligne->quantite }}
                </div>
              </div>
              <div style="font-family:'Amiri',serif;font-size:18px;font-weight:700;
                          color:var(--or-dark);text-align:right;">
                {{ number_format($ligne->sous_total, 2) }}<br>
                <span style="font-size:12px;font-weight:400;font-family:'DM Sans',sans-serif;
                             color:var(--gris-doux);">MAD</span>
              </div>
            </div>
          @endforeach

          {{-- Totaux --}}
          <div style="margin-top:16px;">
            <div style="display:flex;justify-content:space-between;font-size:14px;
                        padding:8px 0;border-bottom:1px solid var(--sable-dark);">
              <span>Sous-total HT</span>
              <span>{{ number_format($commande->total_ht, 2) }} MAD</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:14px;
                        padding:8px 0;border-bottom:1px solid var(--sable-dark);">
              <span>TVA ({{ round($commande->tva * 100) }}%)</span>
              <span>{{ number_format($commande->total_ttc - $commande->total_ht, 2) }} MAD</span>
            </div>
            <div style="display:flex;justify-content:space-between;
                        font-family:'Amiri',serif;font-size:22px;
                        font-weight:700;color:var(--or-dark);padding:14px 0 0;">
              <span>Total TTC</span>
              <span>{{ number_format($commande->total_ttc, 2) }} MAD</span>
            </div>
          </div>
        </div>

        {{-- Historique livraison --}}
        @if($commande->livraison && $commande->livraison->historique->count() > 0)
          <div class="card-tissu p-4">
            <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:20px;">
              Historique de livraison
            </h3>
            @foreach($commande->livraison->historique->sortByDesc('created_at') as $hist)
              <div style="display:flex;gap:14px;padding:10px 0;
                          border-bottom:1px solid var(--sable-dark);">
                <div style="width:10px;height:10px;border-radius:50%;
                            background:var(--or);flex-shrink:0;margin-top:5px;"></div>
                <div>
                  <div style="font-weight:500;font-size:14px;">{{ $hist->commentaire }}</div>
                  @if($hist->localisation)
                    <div style="font-size:12px;color:var(--gris-doux);">
                      <i class="bi bi-geo-alt me-1"></i>{{ $hist->localisation }}
                    </div>
                  @endif
                  <div style="font-size:12px;color:var(--gris-doux);margin-top:3px;">
                    {{ $hist->created_at?->format('d/m/Y H:i') }}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Infos latérales --}}
      <div class="col-lg-4">
        {{-- Adresse --}}
        <div class="card-tissu p-4 mb-3">
          <h5 style="font-size:14px;font-weight:700;margin-bottom:12px;
                     letter-spacing:0.5px;text-transform:uppercase;color:var(--gris-doux);">
            Adresse de livraison
          </h5>
          <div style="font-size:14px;color:var(--texte);line-height:1.8;">
            <strong>{{ auth()->user()->nom_complet }}</strong><br>
            {{ $commande->adresse_livraison }}<br>
            {{ $commande->ville }}
            @if($commande->code_postal) - {{ $commande->code_postal }} @endif
          </div>
          @if($commande->notes)
            <div style="margin-top:10px;font-size:13px;color:var(--gris-doux);
                        background:var(--sable);padding:10px;border-radius:var(--radius-sm);">
              <i class="bi bi-chat-text me-1"></i>{{ $commande->notes }}
            </div>
          @endif
        </div>

        {{-- Paiement --}}
        @if($commande->paiement)
          <div class="card-tissu p-4 mb-3">
            <h5 style="font-size:14px;font-weight:700;margin-bottom:12px;
                       letter-spacing:0.5px;text-transform:uppercase;color:var(--gris-doux);">
              Paiement
            </h5>
            @php
              $pIcons  = ['livraison'=>'💵','carte'=>'💳','virement'=>'🏦','cmi'=>'💳'];
              $pLabels = ['livraison'=>'À la livraison','carte'=>'Carte bancaire',
                          'virement'=>'Virement','cmi'=>'CMI'];
              $pStatus = ['pending'=>['badge-pending','En attente'],
                          'paid'=>['badge-delivered','Payé ✓'],
                          'failed'=>['badge-cancelled','Échoué'],
                          'refunded'=>['badge-processing','Remboursé']];
            @endphp
            <div style="display:flex;align-items:center;gap:12px;">
              <span style="font-size:28px;">{{ $pIcons[$commande->paiement->methode] ?? '💳' }}</span>
              <div>
                <div style="font-weight:600;font-size:14px;">
                  {{ $pLabels[$commande->paiement->methode] ?? $commande->paiement->methode }}
                </div>
                <span class="badge-statut {{ $pStatus[$commande->paiement->statut][0] ?? '' }}"
                      style="font-size:12px;margin-top:4px;display:inline-block;">
                  {{ $pStatus[$commande->paiement->statut][1] ?? $commande->paiement->statut }}
                </span>
              </div>
            </div>
            @if($commande->paiement->reference)
              <div style="margin-top:10px;font-size:12px;color:var(--gris-doux);">
                Réf: {{ $commande->paiement->reference }}
              </div>
            @endif
          </div>
        @endif

        {{-- Actions --}}
        @if(in_array($commande->statut, ['pending','confirmed']))
          <form method="POST" action="{{ route('commandes.annuler', $commande->id) }}">
            @csrf
            <button type="submit" class="btn-outline-or w-100"
                    style="padding:11px;color:var(--rouge-fes);border-color:var(--rouge-fes);"
                    onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
              <i class="bi bi-x-circle me-2"></i>Annuler la commande
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection