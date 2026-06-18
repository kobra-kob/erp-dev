@extends('layouts.app')
@section('title', 'Modifier le bien')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('properties.index') }}" class="text-decoration-none">Biens</a></li>
        <li class="breadcrumb-item active">{{ $property->title }}</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Modifier le bien</h1>

    <form method="POST" action="{{ route('properties.update', $property) }}">
        @method('PUT')
        @include('properties._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('properties.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    <form method="POST" action="{{ route('properties.destroy', $property) }}" class="mt-3" onsubmit="return confirm('Supprimer ce bien ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
