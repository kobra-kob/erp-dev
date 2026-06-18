@extends('layouts.app')
@section('title', 'Modifier l\'ordonnance')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('prescriptions.index') }}" class="text-decoration-none">Ordonnances</a></li>
        <li class="breadcrumb-item active">{{ $prescription->client?->name }}</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Modifier l'ordonnance</h1>

    <form method="POST" action="{{ route('prescriptions.update', $prescription) }}">
        @method('PUT')
        @include('prescriptions._form')
        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('prescriptions.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
