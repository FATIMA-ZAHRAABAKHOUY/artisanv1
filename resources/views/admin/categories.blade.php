@extends('layouts.admin')
@section('title', 'Catégories')

@section('breadcrumb')
    <li class="breadcrumb-item active text-light">Catégories</li>
@endsection

@section('content')
<h1 class="admin-page-title mb-4">Catégories</h1>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">
    <div class="col-lg-4">
        <div class="dash-card">
            <h3 class="h6 fw-bold mb-3">Ajouter une catégorie</h3>
            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="text-muted small">Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" class="form-control" placeholder="Ex: Tapis" required>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Catégorie parente</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— Catégorie principale —</option>
                        @foreach(\App\Models\Categorie::whereNull('parent_id')->get() as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id')==$cat->id ? 'selected':'' }}>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Image</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                <button type="submit" class="btn btn-admin-primary w-100 btn-admin-sm"><i class="fa-solid fa-plus me-1"></i> Ajouter</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="admin-table-wrap">
            <table class="table table-dash table-hover mb-0">
                <thead>
                    <tr><th>Catégorie</th><th>Parent</th><th>Slug</th><th>Produits</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Categorie::with(['parent'])->withCount('produits')->orderBy('nom')->get() as $cat)
                    <tr>
                        <td>
                            <div class="small fw-semibold">{{ $cat->nom }}</div>
                            @if($cat->description)<div class="text-muted" style="font-size:.75rem;">{{ str($cat->description)->limit(40) }}</div>@endif
                        </td>
                        <td class="text-muted small">{{ $cat->parent?->nom ?? '—' }}</td>
                        <td class="text-muted small font-monospace">{{ $cat->slug }}</td>
                        <td class="text-center fw-semibold">{{ $cat->produits_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" onsubmit="return confirm('Supprimer {{ $cat->nom }} ?')">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger-sm btn-admin-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune catégorie</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
