@extends('layouts.app')
@section('title', 'Finaliser la commande — Tissu Artisanal')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('panier.index') }}">Panier</a></li>
  <li class="breadcrumb-item active">Commande</li>
@endsection

@push('styles')
<style>
.checkout-wrap { padding: 48px 0 80px; }
.step-indicator {
    display: flex; gap: 0; margin-bottom: 40px; background: white;
    border-radius: var(--radius); border: 1px solid var(--sable-dark);
    overflow: hidden;
}
.step {
    flex: 1; padding: 16px 12px; text-align: center; font-size: 13px;
    font-weight: 500; color: var(--gris-doux); position: relative;
    border-right: 1px solid var(--sable-dark);
}
.step:last-child { border-right: none; }
.step.active { background: var(--or); color: white; }
.step.done   { background: var(--vert-atlas); color: white; }
.step-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: rgba(255,255,255,0.3); display: inline-flex;
    align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; margin-right: 8px;
}
.methode-card {
    border: 2px solid var(--sable-dark); border-radius: 10px;
    padding: 18px 16px; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 14px;
}
.methode-card:hover    { border-color: var(--or); background: var(--sable); }
.methode-card.selected { border-color: var(--or); background: rgba(200,145,58,0.06); }
.methode-card input    { accent-color: var(--or); width: 18px; height: 18px; flex-shrink:0; }
.methode-icon { font-size: 28px; flex-shrink: 0; }
.methode-title { font-weight: 600; font-size: 15px; }
.methode-desc  { font-size: 12.5px; color: var(--gris-doux); margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="checkout-wrap">
  <div class="container-xl">

    {{-- Étapes --}}
    <div class="step-indicator">
      <div class="step done"><span class="step-num">✓</span>Panier</div>
      <div class="step active"><span class="step-num">2</span>Livraison & Paiement</div>
      <div class="step"><span class="step-num">3</span>Confirmation</div>
    </div>

    @if($errors->any())
      <div class="alert-tissu error mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
      @csrf
      <div class="row g-4">

        {{-- Gauche : formulaire --}}
        <div class="col-lg-7">

          {{-- Adresse livraison --}}
          <div class="card-tissu p-4 mb-4">
            <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:20px;">
              <i class="bi bi-geo-alt me-2 text-muted"></i>Adresse de livraison
            </h3>
            <div class="mb-3">
              <label class="form-label-tissu">Adresse complète *</label>
              <textarea name="adresse_livraison" class="form-control-tissu" rows="2"
                        placeholder="Rue, numéro, appartement…" required>{{ old('adresse_livraison', auth()->user()->adresse) }}</textarea>
            </div>
            <div class="row g-3">
              <div class="col-7">
                <label class="form-label-tissu">Ville *</label>
                <input type="text" name="ville" value="{{ old('ville', auth()->user()->ville) }}"
                       class="form-control-tissu" placeholder="Casablanca" required>
              </div>
              <div class="col-5">
                <label class="form-label-tissu">Code postal</label>
                <input type="text" name="code_postal" value="{{ old('code_postal', auth()->user()->code_postal) }}"
                       class="form-control-tissu" placeholder="20000">
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label-tissu">Notes pour le livreur (optionnel)</label>
              <textarea name="notes" class="form-control-tissu" rows="2"
                        placeholder="Instructions spéciales, horaires…">{{ old('notes') }}</textarea>
            </div>
          </div>

          {{-- Mode de paiement --}}
          <div class="card-tissu p-4">
            <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:20px;">
              <i class="bi bi-credit-card me-2 text-muted"></i>Mode de paiement
            </h3>
            <div class="d-flex flex-column gap-3">
              @foreach([
                ['livraison','Paiement à la livraison','💵','Payez en espèces à la réception de votre commande'],
                ['carte','Carte bancaire','💳','Paiement sécurisé par carte (CMI)'],
                ['virement','Virement bancaire','🏦','Virement sur le compte de la coopérative'],
              ] as [$val,$nom,$icon,$desc])
              <label class="methode-card {{ old('methode')==$val || ($val=='livraison'&&!old('methode')) ? 'selected' : '' }}">
                <input type="radio" name="methode" value="{{ $val }}"
                       {{ old('methode',$val=='livraison'?'livraison':'') == $val ? 'checked' : '' }}>
                <span class="methode-icon">{{ $icon }}</span>
                <div>
                  <div class="methode-title">{{ $nom }}</div>
                  <div class="methode-desc">{{ $desc }}</div>
                </div>
              </label>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Droite : résumé --}}
        <div class="col-lg-5">
          <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                      padding:28px;position:sticky;top:90px;">
            <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:20px;">
              Résumé de la commande
            </h3>

            @php
              $sousTotal = collect(session('panier',[])) ->sum(fn($i) => $i['prix'] * $i['quantite']);
              $tva       = round($sousTotal * 0.20, 2);
              $total     = $sousTotal + $tva;
            @endphp

            <div style="max-height:280px;overflow-y:auto;margin-bottom:16px;">
              @foreach(session('panier', []) as $id => $item)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;
                            border-bottom:1px solid var(--sable-dark);">
                  <div style="width:46px;height:46px;border-radius:8px;background:var(--sable);
                              overflow:hidden;flex-shrink:0;display:flex;align-items:center;
                              justify-content:center;font-size:22px;">
                    @if(!empty($item['image']))
                      <img src="{{ asset('storage/'.$item['image']) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                    @else 🧵 @endif
                  </div>
                  <div style="flex:1;">
                    <div style="font-size:13px;font-weight:500;">{{ Str::limit($item['nom'], 30) }}</div>
                    <div style="font-size:12px;color:var(--gris-doux);">Qté : {{ $item['quantite'] }}</div>
                  </div>
                  <div style="font-size:14px;font-weight:600;color:var(--or-dark);">
                    {{ number_format($item['prix'] * $item['quantite'], 2) }} MAD
                  </div>
                </div>
              @endforeach
            </div>

            <div style="display:flex;justify-content:space-between;font-size:14px;
                        padding:8px 0;border-bottom:1px solid var(--sable-dark);">
              <span>Sous-total HT</span>
              <span>{{ number_format($sousTotal, 2) }} MAD</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:14px;
                        padding:8px 0;border-bottom:1px solid var(--sable-dark);">
              <span>TVA (20%)</span>
              <span>{{ number_format($tva, 2) }} MAD</span>
            </div>
            <div style="display:flex;justify-content:space-between;
                        font-family:'Amiri',serif;font-size:22px;font-weight:700;
                        color:var(--or-dark);padding:14px 0 0;">
              <span>Total TTC</span>
              <span>{{ number_format($total, 2) }} MAD</span>
            </div>

            <button type="submit" class="btn-or w-100"
                    style="margin-top:20px;padding:14px;font-size:15px;">
              <i class="bi bi-check-circle me-2"></i>Confirmer la commande
            </button>
            <p style="font-size:12px;color:var(--gris-doux);text-align:center;margin-top:12px;">
              <i class="bi bi-shield-check me-1 text-success"></i>
              Commande sécurisée — données protégées
            </p>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.methode-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.methode-card').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      card.querySelector('input').checked = true;
    });
  });
</script>
@endpush