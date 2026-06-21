@extends('layouts.app')
@section('title', 'Support — Tissu Artisanal')
@section('breadcrumb')
  <li class="breadcrumb-item active">Support</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl">
    <div class="row g-5">

      {{-- ── FORMULAIRE ──────────────────────────────── --}}
      <div class="col-lg-5">
        <div style="background:white;border-radius:var(--radius);
                    border:1px solid var(--sable-dark);padding:32px;
                    position:sticky;top:90px;">
          <h3 style="font-family:'Amiri',serif;font-size:20px;margin-bottom:20px;">
            <i class="bi bi-headset me-2" style="color:var(--or);"></i>
            Ouvrir un ticket
          </h3>

          @if(session('success'))
            <div class="alert-tissu success mb-4">
              <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
          @endif
          @if($errors->any())
            <div class="alert-tissu error mb-4">
              <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
            </div>
          @endif

          <form method="POST" action="{{ route('support.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label-tissu">Objet *</label>
              <input type="text" name="objet" value="{{ old('objet') }}"
                     class="form-control-tissu"
                     placeholder="Ex: Problème avec ma livraison" required>
            </div>

            <div class="mb-3">
              <label class="form-label-tissu">Commande / Livraison concernée</label>
              <select name="colis_id" class="form-control-tissu">
                <option value="">-- Optionnel --</option>
                @foreach(auth()->user()->commandes()->with('livraison')->latest()->take(10)->get() as $cmd)
                  @if($cmd->livraison)
                    <option value="{{ $cmd->livraison->id }}"
                      {{ old('colis_id') == $cmd->livraison->id ? 'selected' : '' }}>
                      Commande #{{ $cmd->id }}
                      @if($cmd->livraison->numero_suivi)
                        — {{ $cmd->livraison->numero_suivi }}
                      @endif
                    </option>
                  @endif
                @endforeach
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label-tissu">Description du problème *</label>
              <textarea name="description" class="form-control-tissu" rows="5"
                        placeholder="Décrivez votre problème en détail…" required>{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn-or w-100" style="padding:13px;">
              <i class="bi bi-send me-2"></i>Envoyer ma demande
            </button>
          </form>

          <div style="margin-top:20px;padding:16px;background:var(--sable);
                      border-radius:var(--radius-sm);font-size:13px;color:var(--gris-doux);">
            <i class="bi bi-info-circle me-2" style="color:var(--indigo);"></i>
            Notre équipe vous répond généralement dans les <strong>24 heures ouvrées</strong>.
          </div>
        </div>
      </div>

      {{-- ── MES TICKETS ─────────────────────────────── --}}
      <div class="col-lg-7">
        <div class="section-header mb-4">
          <div class="section-icon"><i class="bi bi-chat-dots"></i></div>
          <div>
            <h2>Mes Tickets</h2>
            <p>{{ $tickets->total() }} demande(s) de support</p>
          </div>
        </div>

        @forelse($tickets as $ticket)
          @php
            $tColors = ['ouvert'=>['#fef3c7','#92400e','🔴'],
                        'en_cours'=>['#eff6ff','#1e40af','🟡'],
                        'resolu'=>['#f0fdf4','#065f46','🟢'],
                        'ferme'=>['#f3f4f6','#6b7280','⚫']];
            [$tbg,$tcol,$ticon] = $tColors[$ticket->statut] ?? $tColors['ouvert'];
          @endphp
          <div style="background:white;border-radius:var(--radius);
                      border:1px solid var(--sable-dark);margin-bottom:14px;
                      overflow:hidden;box-shadow:var(--shadow-sm);">
            {{-- Header --}}
            <div style="background:var(--sable);padding:14px 20px;
                        display:flex;align-items:center;justify-content:space-between;
                        flex-wrap:wrap;gap:8px;">
              <div>
                <span style="font-size:12px;color:var(--gris-doux);">Ticket #{{ $ticket->id }}</span>
                <div style="font-weight:600;font-size:15px;">{{ $ticket->objet }}</div>
              </div>
              <span style="background:{{ $tbg }};color:{{ $tcol }};border-radius:20px;
                           padding:4px 12px;font-size:12px;font-weight:600;">
                {{ $ticon }} {{ ucfirst($ticket->statut) }}
              </span>
            </div>
            {{-- Body --}}
            <div style="padding:16px 20px;">
              <p style="font-size:14px;color:var(--gris-doux);margin-bottom:8px;line-height:1.7;">
                {{ Str::limit($ticket->description, 150) }}
              </p>
              @if($ticket->livraison)
                <div style="font-size:12.5px;color:var(--indigo);">
                  <i class="bi bi-truck me-1"></i>
                  Livraison #{{ $ticket->livraison->id }}
                  @if($ticket->livraison->numero_suivi)
                    — {{ $ticket->livraison->numero_suivi }}
                  @endif
                </div>
              @endif
              <div style="font-size:12px;color:var(--gris-doux);margin-top:8px;">
                <i class="bi bi-clock me-1"></i>
                {{ $ticket->created_at?->diffForHumans() }}
                · {{ $ticket->created_at?->format('d/m/Y H:i') }}
              </div>
            </div>
          </div>
        @empty
          <div style="text-align:center;padding:60px 20px;
                      background:var(--sable);border-radius:var(--radius);">
            <div style="font-size:48px;margin-bottom:12px;">💬</div>
            <p style="color:var(--gris-doux);">Aucun ticket de support pour le moment.</p>
          </div>
        @endforelse

        <div class="d-flex justify-content-center mt-3">
          {{ $tickets->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection