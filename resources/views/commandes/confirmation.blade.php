@extends('layouts.app')
@section('title', 'Commande confirmée !')

@section('content')
<div style="padding:80px 0;text-align:center;">
  <div class="container-xl" style="max-width:600px;">

    <div style="background:white;border-radius:var(--radius);
                border:1px solid var(--sable-dark);padding:56px 40px;
                box-shadow:var(--shadow-lg);">

      {{-- Icône succès animée --}}
      <div style="width:88px;height:88px;border-radius:50%;
                  background:linear-gradient(135deg,var(--vert-atlas),#27ae60);
                  display:flex;align-items:center;justify-content:center;
                  font-size:40px;margin:0 auto 24px;
                  box-shadow:0 0 0 12px rgba(45,106,79,0.1),0 0 0 24px rgba(45,106,79,0.05);">
        ✅
      </div>

      <h1 style="font-family:'Amiri',serif;font-size:32px;margin-bottom:12px;color:var(--vert-atlas);">
        Commande confirmée !
      </h1>
      <p style="color:var(--gris-doux);font-size:16px;line-height:1.75;margin-bottom:28px;">
        Merci pour votre commande. Nous avons bien reçu votre demande
        et nous allons la traiter dans les plus brefs délais.
      </p>

      @if(isset($commande))
        <div style="background:var(--sable);border-radius:var(--radius-sm);
                    padding:20px;margin-bottom:28px;text-align:left;">
          <div style="display:flex;justify-content:space-between;
                      font-size:14px;padding:8px 0;border-bottom:1px solid var(--sable-dark);">
            <span style="color:var(--gris-doux);">Numéro de commande</span>
            <strong style="color:var(--or-dark);">#{{ $commande->id }}</strong>
          </div>
          <div style="display:flex;justify-content:space-between;
                      font-size:14px;padding:8px 0;border-bottom:1px solid var(--sable-dark);">
            <span style="color:var(--gris-doux);">Montant total</span>
            <strong>{{ number_format($commande->total_ttc, 2) }} MAD</strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:14px;padding:8px 0;">
            <span style="color:var(--gris-doux);">Mode de paiement</span>
            <strong>
              @if($commande->paiement?->methode === 'livraison') 💵 À la livraison
              @elseif($commande->paiement?->methode === 'carte') 💳 Carte bancaire
              @else 🏦 Virement @endif
            </strong>
          </div>
        </div>
      @endif

      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('commandes.show', $commande->id ?? 1) }}"
           class="btn-or" style="padding:12px 28px;">
          <i class="bi bi-bag-check me-2"></i>Suivre ma commande
        </a>
        <a href="{{ route('catalogue.index') }}"
           class="btn-outline-or" style="padding:12px 28px;">
          <i class="bi bi-grid me-2"></i>Continuer mes achats
        </a>
      </div>

      <p style="font-size:13px;color:var(--gris-doux);margin-top:24px;">
        <i class="bi bi-envelope me-1"></i>
        Un email de confirmation vous sera envoyé à
        <strong>{{ auth()->user()->email }}</strong>
      </p>
    </div>
  </div>
</div>
@endsection