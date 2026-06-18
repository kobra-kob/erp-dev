@extends('layouts.app')
@section('title', 'Nouvelle intervention')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('interventions.index') }}" class="text-decoration-none">Planning</a></li>
            <li class="breadcrumb-item active">Nouvelle</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouvelle intervention</h1>

    <form method="POST" action="{{ route('interventions.store') }}">
        @include('interventions._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Planifier</button>
            <a href="{{ route('interventions.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
