@extends('layouts.admin')
@section('title', 'Fournisseurs')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Fournisseurs</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h1 class="admin-page-title mb-0">Fournisseurs</h1>
    <a href="{{ route('admin.fournisseurs.create') }}" class="btn btn-admin-primary btn-admin-sm">
        <i class="fa-solid fa-plus me-1"></i> Ajouter
    </a>
</div>

<div class="admin-tabs mb-4">
    @foreach([''=>'Tous','local'=>'Locaux','national'=>'Nationaux','en_ligne'=>'En ligne'] as $v => $l)
    <a href="{{ route('admin.fournisseurs', $v ? ['type'=>$v] : []) }}"
       class="{{ (request('type','')==$v || (request('type')==='' && $v==='')) ? 'active' : '' }}">{{ $l }}</a>
    @endforeach
</div>

<div class="row g-3">
    @forelse($fournisseurs as $f)
    <div class="col-md-6 col-xl-4">
        <div class="dash-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="fw-bold">{{ $f->nom }}</div>
                    <div class="text-muted small mt-1">
                        @if($f->type==='local') Local — {{ $f->ville }}
                        @elseif($f->type==='national') National
                        @else En ligne @endif
                    </div>
                </div>
                <span class="badge-statut-sm badge-{{ $f->statut === 'actif' ? 'actif' : 'inactif' }}">{{ $f->statut }}</span>
            </div>
            @if($f->specialites->count() > 0)
            <div class="d-flex flex-wrap gap-1 mb-2">
                @foreach($f->specialites->take(3) as $spec)
                <span class="badge bg-secondary-subtle text-muted" style="font-size:.7rem;">{{ $spec->specialite }}</span>
                @endforeach
            </div>
            @endif
            <div class="text-muted small mb-3">
                @if($f->telephone)<i class="fa-solid fa-phone me-1"></i>{{ $f->telephone }} @endif
                @if($f->remise_cooperative > 0)<span class="text-success ms-2">{{ $f->remise_cooperative }}% coop.</span>@endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.fournisseurs.edit', $f->id) }}" class="btn btn-sm btn-outline-secondary flex-fill btn-admin-sm">Modifier</a>
                <form method="POST" action="{{ route('admin.fournisseurs.destroy', $f->id) }}" onsubmit="return confirm('Désactiver ce fournisseur ?')">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger-sm btn-admin-sm"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-5">Aucun fournisseur trouvé.</div>
    @endforelse
</div>
<div class="d-flex justify-content-center mt-4">{{ $fournisseurs->withQueryString()->links() }}</div>
@endsection
