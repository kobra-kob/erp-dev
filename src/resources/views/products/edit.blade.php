@extends('layouts.app')
@section('title', 'Modifier le produit')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Stock</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier {{ $product->name }}</h1>

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('products._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('products.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    <form method="POST" action="{{ route('products.destroy', $product) }}" class="mt-3"
          onsubmit="return confirm('Supprimer ce produit ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
