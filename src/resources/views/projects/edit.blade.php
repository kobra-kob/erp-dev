@extends('layouts.app')
@section('title', 'Modifier le chantier')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Chantiers</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}" class="text-decoration-none">{{ $project->name }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier le chantier</h1>

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @method('PUT')
        @include('projects._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-3"
          onsubmit="return confirm('Supprimer ce chantier et ses pièces jointes ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
