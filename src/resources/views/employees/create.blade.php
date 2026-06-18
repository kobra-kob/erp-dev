@extends('layouts.app')
@section('title', 'Nouvel employé')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none">Employés</a></li>
            <li class="breadcrumb-item active">Nouveau</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouvel employé</h1>

    <form method="POST" action="{{ route('employees.store') }}">
        @include('employees._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer</button>
            <a href="{{ route('employees.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
