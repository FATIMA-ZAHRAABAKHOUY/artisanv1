@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Liste des Livreurs</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Livreur</th>
                <th>Téléphone</th>
                <th>Véhicule</th>
                <th>Disponibilité</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livreurs as $livreur)
                <tr>
                    <td>{{ $livreur->id }}</td>
                    <td>{{ $livreur->user->name ?? 'N/A' }}</td>
                    <td>{{ $livreur->telephone }}</td>
                    <td>{{ $livreur->vehicule_type ?? 'Non spécifié' }}</td>
                    <td>
                        <span class="badge {{ $livreur->disponible ? 'bg-success' : 'bg-danger' }}">
                            {{ $livreur->disponible ? 'Disponible' : 'Occupé' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Aucun livreur enregistré pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection