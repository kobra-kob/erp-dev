@extends('layouts.app')
@section('title', 'Employés')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-person-badge-fill text-indigo me-2" style="color:#4f46e5"></i>Employés</h1>
            <p class="text-muted mb-0">
                {{ $employees->total() }} membre(s) ·
                <span class="{{ $company->canAddEmployee() ? '' : 'text-danger fw-semibold' }}">
                    {{ $company->employeeCount() }}/{{ \App\Models\Company::MAX_EMPLOYEES }} employés
                </span>
                (hors propriétaire)
            </p>
        </div>
        @if($company->canAddEmployee())
            <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvel employé</a>
        @else
            <button class="btn btn-primary" disabled title="Limite de {{ \App\Models\Company::MAX_EMPLOYEES }} employés atteinte"><i class="bi bi-plus-lg me-1"></i>Nouvel employé</button>
        @endif
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Compétence</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none">{{ $emp->name }}</a>
                                @if($emp->id === auth()->id())<span class="badge text-bg-light text-dark ms-1">vous</span>@endif
                            </td>
                            <td class="text-muted">{{ $emp->email }}</td>
                            <td><span class="badge text-bg-primary">{{ $emp->roleLabel() }}</span></td>
                            <td>{{ $emp->skill ?: '—' }}</td>
                            <td>
                                @if($emp->is_active)<span class="badge text-bg-success">Actif</span>
                                @else<span class="badge text-bg-secondary">Inactif</span>@endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $employees->links() }}</div>
@endsection
