{{-- ================================================================
     resources/views/notifications/index.blade.php
================================================================ --}}
@extends('layouts.app')
@section('title', 'Mes Notifications')
@section('breadcrumb')
  <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div style="padding:48px 0 80px;">
  <div class="container-xl" style="max-width:720px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div class="section-header mb-0">
        <div class="section-icon"><i class="bi bi-bell"></i></div>
        <div>
          <h2>Notifications</h2>
          <p>{{ $notifications->total() }} notification(s)</p>
        </div>
      </div>
      @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.lire-tout') }}">
          @csrf
          <button type="submit" class="btn-outline-or" style="padding:8px 18px;font-size:13px;">
            <i class="bi bi-check-all me-1"></i>Tout marquer comme lu
          </button>
        </form>
      @endif
    </div>

    @forelse($notifications as $notif)
      <div style="background:{{ $notif->is_read ? 'white' : 'rgba(200,145,58,0.04)' }};
                  border:1px solid {{ $notif->is_read ? 'var(--sable-dark)' : 'var(--or)' }};
                  border-radius:var(--radius);padding:18px 20px;margin-bottom:10px;
                  display:flex;align-items:flex-start;gap:14px;transition:all 0.2s;">
        <div style="width:44px;height:44px;border-radius:50%;
                    background:{{ $notif->is_read ? 'var(--sable)' : 'rgba(200,145,58,0.12)' }};
                    display:flex;align-items:center;justify-content:center;
                    font-size:20px;flex-shrink:0;">
          @php
            $icons=['commande_creee'=>'🛒','commande_statut'=>'📦','paiement_confirme'=>'✅',
                    'livraison_statut'=>'🚚','inscription_formation'=>'🎓',
                    'formation_terminee'=>'🏅','artisan_valide'=>'✨'];
          @endphp
          {{ $icons[$notif->type] ?? '🔔' }}
        </div>
        <div style="flex:1;">
          <div style="font-weight:{{ $notif->is_read ? '400' : '600' }};font-size:14px;margin-bottom:4px;">
            {{ $notif->titre }}
          </div>
          <div style="font-size:13.5px;color:var(--gris-doux);line-height:1.6;">
            {{ $notif->message }}
          </div>
          <div style="font-size:12px;color:var(--gris-doux);margin-top:6px;">
            {{ $notif->created_at?->diffForHumans() }}
          </div>
        </div>
        @if(!$notif->is_read)
          <form method="POST" action="{{ route('notifications.lire', $notif->id) }}" style="flex-shrink:0;">
            @csrf @method('PUT')
            <button type="submit"
                    style="background:none;border:none;cursor:pointer;
                           color:var(--gris-doux);font-size:18px;padding:0;"
                    title="Marquer comme lu">
              <i class="bi bi-check-circle"></i>
            </button>
          </form>
        @endif
      </div>
    @empty
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:64px;margin-bottom:16px;">🔔</div>
        <h3 style="font-family:'Amiri',serif;">Aucune notification</h3>
        <p style="color:var(--gris-doux);">Vous êtes à jour !</p>
      </div>
    @endforelse

    <div class="d-flex justify-content-center mt-4">
      {{ $notifications->links() }}
    </div>
  </div>
</div>
@endsection