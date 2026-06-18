@extends('layouts.app')
@section('title', 'Nouveau bien')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('properties.index') }}" class="text-decoration-none">Biens</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Nouveau bien</h1>

    <form method="POST" action="{{ route('properties.store') }}">
        @include('properties._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('properties.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
