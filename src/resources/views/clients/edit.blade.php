@extends('layouts.app')
@section('title', 'Modifier le client')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none">Clients</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}" class="text-decoration-none">{{ $client->name }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier {{ $client->name }}</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('clients.update', $client) }}">
                @method('PUT')
                @include('clients._form')
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                    <a href="{{ route('clients.show', $client) }}" class="btn btn-light">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="mt-3"
          onsubmit="return confirm('Supprimer définitivement ce client ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer ce client</button>
    </form>
@endsection
