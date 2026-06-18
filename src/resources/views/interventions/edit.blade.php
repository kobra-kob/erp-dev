@extends('layouts.app')
@section('title', 'Modifier l\'intervention')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('interventions.index') }}" class="text-decoration-none">Planning</a></li>
            <li class="breadcrumb-item"><a href="{{ route('interventions.show', $intervention) }}" class="text-decoration-none">{{ $intervention->title }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier l'intervention</h1>

    <form method="POST" action="{{ route('interventions.update', $intervention) }}">
        @method('PUT')
        @include('interventions._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('interventions.show', $intervention) }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    <form method="POST" action="{{ route('interventions.destroy', $intervention) }}" class="mt-3"
          onsubmit="return confirm('Supprimer cette intervention ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
