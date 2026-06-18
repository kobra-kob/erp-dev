@extends('layouts.app')
@section('title', 'Nouveau produit')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Stock</a></li>
            <li class="breadcrumb-item active">Nouveau</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouveau produit</h1>

    <form method="POST" action="{{ route('products.store') }}">
        @include('products._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('products.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
