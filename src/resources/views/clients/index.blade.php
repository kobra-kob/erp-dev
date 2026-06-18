@extends('layouts.app')
@section('title', 'Clients')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Clients</h1>
            <p class="text-muted mb-0">{{ $clients->total() }} client(s) enregistré(s).</p>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau client</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="input-group" style="max-width:420px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Rechercher un client…">
            @if($search)<a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">&times;</a>@endif
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th class="d-none d-md-table-cell">Contact</th>
                        <th class="d-none d-lg-table-cell">Ville</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <a href="{{ route('clients.show', $client) }}" class="fw-semibold text-decoration-none">{{ $client->name }}</a>
                                @if($client->contact_name)<div class="text-muted small">{{ $client->contact_name }}</div>@endif
                            </td>
                            <td>
                                <span class="badge {{ $client->type === 'professionnel' ? 'text-bg-info' : 'text-bg-secondary' }}">
                                    {{ $client->typeLabel() }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($client->phone)<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $client->phone }}</div>@endif
                                @if($client->email)<div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $client->email }}</div>@endif
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $client->city ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun client {{ $search ? 'ne correspond à votre recherche' : 'pour le moment' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $clients->links() }}</div>
@endsection
