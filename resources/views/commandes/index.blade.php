{{-- resources/views/commandes/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Mes Commandes')

@section('breadcrumb')
  <li class="breadcrumb-item active">Mes commandes</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">

    <div class="section-header mb-4">
      <div class="section-icon"><i class="bi bi-bag-check"></i></div>
      <div>
        <h2>Mes Commandes</h2>
        <p>Historique et suivi de vos commandes</p>
      </div>
    </div>

    {{-- Filtres statut --}}
    <div class="d-flex gap-2 flex-wrap mb-4">
      @foreach(['tous'=>'Toutes','pending'=>'En attente','confirmed'=>'Confirmées',
                'shipped'=>'Expédiées','delivered'=>'Livrées','cancelled'=>'Annulées'] as $val => $lbl)
        <a href="{{ route('commandes.index', $val != 'tous' ? ['statut'=>$val] : []) }}"
           class="cat-chip {{ request('statut',$val=='tous'?'tous':null) == $val || (!request('statut')&&$val=='tous') ? 'active' : '' }}">
          {{ $lbl }}
        </a>
      @endforeach
    </div>

    @forelse($commandes as $commande)
      <div style="background:white;border-radius:var(--radius);border:1px solid var(--sable-dark);
                  margin-bottom:14px;overflow:hidden;box-shadow:var(--shadow-sm);">
        {{-- Header commande --}}
        <div style="background:var(--sable);padding:14px 20px;display:flex;
                    align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
          <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div>
              <span style="font-size:12px;color:var(--gris-doux);">COMMANDE #</span>
              <strong style="font-size:15px;">{{ $commande->id }}</strong>
            </div>
            <div style="font-size:13px;color:var(--gris-doux);">
              <i class="bi bi-calendar3 me-1"></i>
              {{ $commande->created_at?->format('d/m/Y H:i') }}
            </div>
          </div>
          <div class="d-flex align-items-center gap-10">
            @php
              $badges = [
                'pending'=>'badge-pending','confirmed'=>'badge-confirmed',
                'processing'=>'badge-processing','shipped'=>'badge-shipped',
                'delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'
              ];
              $labels = [
                'pending'=>'⏳ En attente','confirmed'=>'✅ Confirmée',
                'processing'=>'📦 Préparation','shipped'=>'🚚 Expédiée',
                'delivered'=>'✔️ Livrée','cancelled'=>'❌ Annulée'
              ];
            @endphp
            <span class="badge-statut {{ $badges[$commande->statut] ?? '' }}">
              {{ $labels[$commande->statut] ?? $commande->statut }}
            </span>
          </div>
        </div>

        {{-- Articles (résumé) --}}
        <div style="padding:16px 20px;display:flex;align-items:center;
                    justify-content:space-between;flex-wrap:wrap;gap:14px;">
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @foreach($commande->lignes->take(4) as $ligne)
              <div style="width:52px;height:52px;border-radius:8px;background:var(--sable);
                          overflow:hidden;border:1px solid var(--sable-dark);
                          display:flex;align-items:center;justify-content:center;font-size:20px;">
                @if(!empty($ligne->produit?->images[0]))
                  <img src="{{ asset('storage/'.$ligne->produit->images[0]) }}"
                       alt="" style="width:100%;height:100%;object-fit:cover;">
                @else 🧵 @endif
              </div>
            @endforeach
            @if($commande->lignes->count() > 4)
              <div style="width:52px;height:52px;border-radius:8px;background:var(--sable-dark);
                          display:flex;align-items:center;justify-content:center;
                          font-size:13px;font-weight:600;color:var(--gris-doux);">
                +{{ $commande->lignes->count() - 4 }}
              </div>
            @endif
          </div>

          <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
            <div style="text-align:right;">
              <div style="font-size:12px;color:var(--gris-doux);">Total TTC</div>
              <div style="font-family:'Amiri',serif;font-size:22px;font-weight:700;color:var(--or-dark);">
                {{ number_format($commande->total_ttc, 2) }} MAD
              </div>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('commandes.show', $commande->id) }}" class="btn-or"
                 style="padding:9px 18px;font-size:13px;">
                Détails
              </a>
              @if(in_array($commande->statut, ['pending','confirmed']))
                <form method="POST" action="{{ route('commandes.annuler', $commande->id) }}">
                  @csrf
                  <button type="submit" class="btn-outline-or"
                          style="padding:9px 16px;font-size:13px;color:var(--rouge-fes);border-color:var(--rouge-fes);"
                          onclick="return confirm('Annuler cette commande ?')">
                    Annuler
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:64px;margin-bottom:16px;">📦</div>
        <h3 style="font-family:'Amiri',serif;">Aucune commande</h3>
        <p style="color:var(--gris-doux);margin-bottom:28px;">Vous n'avez pas encore passé de commande</p>
        <a href="{{ route('catalogue.index') }}" class="btn-or">
          <i class="bi bi-grid me-2"></i>Découvrir le catalogue
        </a>
      </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
      {{ $commandes->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection


{{-- ================================================================
     resources/views/commandes/show.blade.php
================================================================ --}}