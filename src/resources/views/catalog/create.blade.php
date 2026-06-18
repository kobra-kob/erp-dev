@extends('layouts.app')
@section('title', 'Nouvelle prestation')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}" class="text-decoration-none">Catalogue</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Nouvelle prestation</h1>

    <form method="POST" action="{{ route('catalog.store') }}">
        @include('catalog._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('catalog.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
