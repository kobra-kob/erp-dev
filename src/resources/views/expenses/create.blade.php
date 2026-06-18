@extends('layouts.app')
@section('title', 'Nouvelle dépense')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none">Dépenses</a></li>
            <li class="breadcrumb-item active">Nouvelle</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouvelle dépense</h1>

    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
        @include('expenses._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
