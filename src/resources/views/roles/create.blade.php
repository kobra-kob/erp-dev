@extends('layouts.app')
@section('title', 'Nouveau rôle')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-decoration-none">Rôles &amp; accès</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Nouveau rôle</h1>

    <form method="POST" action="{{ route('roles.store') }}">
        @include('roles._form')
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer le rôle</button>
            <a href="{{ route('roles.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
