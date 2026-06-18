@extends('layouts.app')
@section('title', 'Modifier le rôle')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-decoration-none">Rôles &amp; accès</a></li>
        <li class="breadcrumb-item active">{{ $role->name }}</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Modifier le rôle « {{ $role->name }} »</h1>

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @method('PUT')
        @include('roles._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('roles.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
