@extends('layouts.app')
@section('title', 'Modifier le véhicule')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}" class="text-decoration-none">Parc véhicules</a></li>
        <li class="breadcrumb-item active">{{ $vehicle->name() }}</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Modifier le véhicule</h1>

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
        @method('PUT')
        @include('vehicles._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" class="mt-3" onsubmit="return confirm('Supprimer ce véhicule ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
