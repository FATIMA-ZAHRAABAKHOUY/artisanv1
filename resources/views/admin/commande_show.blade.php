@extends('layouts.admin')
@section('title', 'Commande #'.$commande->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.commandes') }}" class="text-muted text-decoration-none">Commandes</a></li>
    <li class="breadcrumb-item active text-light">#{{ $commande->id }}</li>
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="{{ route('admin.commandes') }}" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Retour</a>
    <h1 class="admin-page-title mb-0">Commande #{{ $commande->id }}</h1>
      @php
        $bMap=['pending'=>'badge-pending','confirmed'=>'badge-confirmed',
               'processing'=>'badge-processing','shipped'=>'badge-shipped',
               'delivered'=>'badge-delivered','cancelled'=>'badge-cancelled'];
      @endphp
      <span class="badge-statut {{ $bMap[$commande->statut] ?? '' }}">
        {{ $commande->statut }}
      </span>
    </div>

    <div class="row g-4">
      {{-- Lignes commande --}}
      <div class="col-lg-8">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:24px;margin-bottom:16px;">
          <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:18px;">
            Articles commandés
          </h3>
          @foreach($commande->lignes as $ligne)
            <div style="display:flex;align-items:center;gap:14px;
                        padding:14px 0;border-bottom:1px solid var(--sable-dark);">
              <div style="width:56px;height:56px;border-radius:8px;background:var(--sable);
                          flex-shrink:0;overflow:hidden;display:flex;align-items:center;
                          justify-content:center;font-size:24px;">
                @if(!empty($ligne->produit?->images[0]))
                  <img src="{{ asset('storage/'.$ligne->produit->images[0]) }}"
                       style="width:100%;height:100%;object-fit:cover;" alt="">
                @else 🧵 @endif
              </div>
              <div style="flex:1;">
                <div style="font-weight:600;font-size:14px;">{{ $ligne->produit?->nom }}</div>
                <div style="font-size:12px;color:var(--gris-doux);">
                  Artisan : {{ $ligne->produit?->artisan?->user?->nom_complet }}
                </div>
                <div style="font-size:13px;color:var(--gris-doux);margin-top:3px;">
                  {{ number_format($ligne->prix_unitaire,2) }} MAD × {{ $ligne->quantite }}
                </div>
              </div>
              <div style="font-family:'Amiri',serif;font-size:18px;
                          font-weight:700;color:var(--or-dark);text-align:right;">
                {{ number_format($ligne->sous_total,2) }}<br>
                <span style="font-size:12px;font-family:'DM Sans',sans-serif;
                             font-weight:400;color:var(--gris-doux);">MAD</span>
              </div>
            </div>
          @endforeach

          {{-- Totaux --}}
          <div style="margin-top:16px;">
            @foreach([
              ['Sous-total HT', number_format($commande->total_ht,2).' MAD'],
              ['TVA ('.round($commande->tva*100).'%)', number_format($commande->total_ttc - $commande->total_ht,2).' MAD'],
            ] as [$lbl,$val])
              <div style="display:flex;justify-content:space-between;font-size:14px;
                          padding:7px 0;border-bottom:1px solid var(--sable-dark);">
                <span>{{ $lbl }}</span><span>{{ $val }}</span>
              </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;
                        font-family:'Amiri',serif;font-size:22px;
                        font-weight:700;color:var(--or-dark);padding:12px 0 0;">
              <span>Total TTC</span>
              <span>{{ number_format($commande->total_ttc,2) }} MAD</span>
            </div>
          </div>
        </div>

        {{-- Historique livraison --}}
        @if($commande->livraison?->historique?->count() > 0)
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);padding:24px;">
            <h3 style="font-family:'Amiri',serif;font-size:18px;margin-bottom:16px;">
              Historique livraison
            </h3>
            @foreach($commande->livraison->historique->sortByDesc('created_at') as $h)
              <div style="display:flex;gap:12px;padding:10px 0;
                          border-bottom:1px solid var(--sable-dark);">
                <div style="width:10px;height:10px;border-radius:50%;
                            background:var(--or);margin-top:5px;flex-shrink:0;"></div>
                <div>
                  <div style="font-weight:500;font-size:14px;">{{ $h->commentaire }}</div>
                  <div style="font-size:12px;color:var(--gris-doux);">
                    {{ $h->modifiePar?->nom_complet ?? 'Système' }}
                    · {{ $h->created_at?->format('d/m/Y H:i') }}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Infos latérales --}}
      <div class="col-lg-4">
        {{-- Client --}}
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:20px;margin-bottom:12px;">
          <h5 style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;
                     color:var(--gris-doux);font-weight:700;margin-bottom:12px;">Client</h5>
          <div style="font-weight:600;font-size:15px;">{{ $commande->client?->nom_complet }}</div>
          <div style="font-size:13px;color:var(--gris-doux);margin-top:4px;">{{ $commande->client?->email }}</div>
          <div style="font-size:13px;color:var(--gris-doux);">{{ $commande->client?->telephone }}</div>
        </div>

        {{-- Adresse --}}
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:20px;margin-bottom:12px;">
          <h5 style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;
                     color:var(--gris-doux);font-weight:700;margin-bottom:12px;">Adresse de livraison</h5>
          <div style="font-size:14px;line-height:1.8;">
            {{ $commande->adresse_livraison }}<br>
            {{ $commande->ville }}
            @if($commande->code_postal) — {{ $commande->code_postal }} @endif
          </div>
          @if($commande->notes)
            <div style="margin-top:8px;font-size:13px;color:var(--gris-doux);
                        background:var(--sable);padding:8px;border-radius:var(--radius-sm);">
              {{ $commande->notes }}
            </div>
          @endif
        </div>

        {{-- Paiement --}}
        @if($commande->paiement)
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);padding:20px;margin-bottom:12px;">
            <h5 style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;
                       color:var(--gris-doux);font-weight:700;margin-bottom:12px;">Paiement</h5>
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="font-size:24px;">{{ ['livraison'=>'💵','carte'=>'💳','virement'=>'🏦'][$commande->paiement->methode] ?? '💳' }}</span>
              <div>
                <div style="font-weight:600;font-size:14px;">{{ ucfirst($commande->paiement->methode) }}</div>
                <span class="badge-statut {{ $commande->paiement->estPaye() ? 'badge-delivered':'badge-pending' }}"
                      style="font-size:11px;display:inline-block;margin-top:3px;">
                  {{ $commande->paiement->estPaye() ? '✓ Payé' : 'En attente' }}
                </span>
              </div>
            </div>
            @if($commande->paiement->reference)
              <div style="margin-top:8px;font-size:12px;color:var(--gris-doux);">
                Réf: {{ $commande->paiement->reference }}
              </div>
            @endif
          </div>
        @endif

        {{-- Livraison --}}
        @if($commande->livraison)
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);padding:20px;margin-bottom:12px;">
            <h5 style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;
                       color:var(--gris-doux);font-weight:700;margin-bottom:12px;">Livraison</h5>
            <div style="font-size:13px;line-height:1.8;">
              <div>Statut : <strong>{{ $commande->livraison->statut }}</strong></div>
              @if($commande->livraison->livreur)
                <div>Livreur : <strong>{{ $commande->livraison->livreur->nom_complet }}</strong></div>
              @else
                <div style="color:var(--rouge-fes);">⚠️ Aucun livreur assigné</div>
              @endif
              @if($commande->livraison->numero_suivi)
                <div>Suivi : <strong>{{ $commande->livraison->numero_suivi }}</strong></div>
              @endif
              @if($commande->livraison->date_livraison_prevue)
                <div>Prévue le : <strong>{{ $commande->livraison->date_livraison_prevue?->format('d/m/Y') }}</strong></div>
              @endif
            </div>

            @if(!$commande->livraison->livreur_id)
              <a href="{{ route('admin.livraisons.assigner.form', $commande->livraison->id) }}"
                 class="btn-or w-100"
                 style="margin-top:12px;padding:9px;display:block;text-align:center;font-size:13px;">
                <i class="bi bi-person-plus me-1"></i>Assigner un livreur
              </a>
            @endif
          </div>
        @endif

        {{-- Changer statut --}}
        @if(!in_array($commande->statut, ['delivered','cancelled']))
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);padding:20px;">
            <h5 style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;
                       color:var(--gris-doux);font-weight:700;margin-bottom:12px;">Changer le statut</h5>
            <form method="POST" action="{{ route('admin.commandes.statut', $commande->id) }}"
                  style="display:flex;gap:8px;">
              @csrf @method('PUT')
              <select name="statut" class="form-control-tissu"
                      style="padding:8px 12px;font-size:13px;flex:1;">
                @foreach(['confirmed','processing','shipped','delivered','cancelled'] as $s)
                  <option value="{{ $s }}" {{ $commande->statut==$s ? 'selected':'' }}>
                    {{ ucfirst($s) }}
                  </option>
                @endforeach
              </select>
              <button type="submit" class="btn-or" style="padding:8px 16px;font-size:13px;">
                OK
              </button>
            </form>
          </div>
        @endif
      </div>
    </div>
@endsection