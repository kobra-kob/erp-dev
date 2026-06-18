@extends('layouts.app')
@section('title', 'Nouveau client')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none">Clients</a></li>
            <li class="breadcrumb-item active">Nouveau</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouveau client</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('clients.store') }}">
                @include('clients._form')
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                    <a href="{{ route('clients.index') }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
