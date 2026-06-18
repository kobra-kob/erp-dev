@extends('layouts.app')
@section('title', 'Modifier le devis')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}" class="text-decoration-none">Devis</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quotes.show', $quote) }}" class="text-decoration-none">{{ $quote->number }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier {{ $quote->number }}</h1>

    <form method="POST" action="{{ route('quotes.update', $quote) }}">
        @method('PUT')
        @include('quotes._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
