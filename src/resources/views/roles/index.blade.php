@extends('layouts.app')
@section('title', 'Rôles & accès')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Rôles &amp; accès</h1>
            <p class="text-muted mb-0">Créez des rôles personnalisés et choisissez les modules accessibles.</p>
        </div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau rôle</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted mb-3">Rôles personnalisés</h2>
            @forelse($roles as $role)
                <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom py-2 gap-2">
                    <div>
                        <span class="fw-semibold">{{ $role->name }}</span>
                        <span class="text-muted small">· {{ $role->users_count }} employé(s)</span>
                        <div class="mt-1">
                            @forelse($role->modules ?? [] as $key)
                                <span class="badge text-bg-light text-dark">{{ config("modules.$key.label", $key) }}</span>
                            @empty
                                <span class="text-muted small">Aucun module</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Supprimer ce rôle ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">Aucun rôle personnalisé. Créez-en un pour donner des accès sur mesure.</p>
            @endforelse
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted mb-3">Rôles intégrés</h2>
            <ul class="mb-0 text-muted small">
                <li><strong>Administrateur</strong> — accès complet (gère l'entreprise, les employés et les rôles).</li>
                <li><strong>Gérant</strong> — clients, devis, factures, stock, dépenses, compta, planning, chantiers…</li>
                <li><strong>Employé</strong> — planning, chantiers, documents, congés.</li>
            </ul>
        </div>
    </div>
@endsection
