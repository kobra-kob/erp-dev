@extends('layouts.app')
@section('title', 'Modifier l\'employé')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none">Employés</a></li>
            <li class="breadcrumb-item active">{{ $employee->name }}</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier {{ $employee->name }}</h1>

    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @method('PUT')
        @include('employees._form')
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('employees.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>

    @if($employee->id !== auth()->id())
        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="mt-3"
              onsubmit="return confirm('Supprimer cet employé ?');">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
        </form>
    @endif
@endsection
