@extends('layouts.app')
@section('title', 'Nouveau devis')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}" class="text-decoration-none">Devis</a></li>
            <li class="breadcrumb-item active">Nouveau</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouveau devis</h1>

    <form method="POST" action="{{ route('quotes.store') }}">
        @include('quotes._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer le devis</button>
            <a href="{{ route('quotes.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
