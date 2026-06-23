@extends('support.layouts.app')
@section('title', 'Tenants')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-bg-dark border-secondary"><div class="card-body">
                <div class="text-white-50 small text-uppercase">Tenants</div>
                <div class="display-6">{{ $totalTenants }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-bg-dark border-secondary"><div class="card-body">
                <div class="text-white-50 small text-uppercase">En ligne (&lt; {{ $onlineWindow }} min)</div>
                <div class="display-6 text-success">{{ $onlineTenants }}</div>
            </div></div>
        </div>
    </div>

    <div class="card border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID support</th>
                        <th>Entreprise</th>
                        <th>Propriétaire</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Clients</th>
                        <th>Statut</th>
                        <th>Dernière activité</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        @php
                            $seen = $company->last_seen_at ? \Illuminate\Support\Carbon::parse($company->last_seen_at) : null;
                            $online = $seen && $seen->gte(now()->subMinutes($onlineWindow));
                        @endphp
                        <tr>
                            <td><code class="text-info">{{ $company->supportId() }}</code></td>
                            <td>
                                <a href="{{ route('support.tenants.show', $company) }}" class="text-decoration-none text-white fw-semibold">
                                    {{ $company->name }}
                                </a>
                            </td>
                            <td class="small text-white-50">{{ $company->owner?->email ?? '—' }}</td>
                            <td class="text-center">{{ $company->users_count }}</td>
                            <td class="text-center">{{ $company->clients_count }}</td>
                            <td>
                                @if ($company->status === 'suspended')
                                    <span class="badge text-bg-warning">Suspendu</span>
                                @else
                                    <span class="badge {{ $online ? 'badge-online' : 'badge-offline' }}">{{ $online ? 'En ligne' : 'Hors ligne' }}</span>
                                @endif
                            </td>
                            <td class="small text-white-50">{{ $seen ? $seen->diffForHumans() : 'jamais' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-white-50 py-4">Aucun tenant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $companies->links() }}</div>
@endsection
