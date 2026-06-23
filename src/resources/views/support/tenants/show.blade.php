@extends('support.layouts.app')
@section('title', $company->name)

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <a href="{{ route('support.tenants.index') }}" class="text-decoration-none text-white-50 small">
                <i class="bi bi-arrow-left"></i> Tous les tenants
            </a>
            <h1 class="h3 mt-1 text-white">{{ $company->name }}</h1>
            <code class="text-info">{{ $company->supportId() }}</code>
            @if ($company->status === 'suspended')
                <span class="badge text-bg-warning ms-2">Suspendu</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-secondary text-bg-dark mb-3">
                <div class="card-header">Coordonnées</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between"><span class="text-white-50">SIRET</span><span>{{ $company->siret ?? '—' }}</span></li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between"><span class="text-white-50">E-mail</span><span>{{ $company->email ?? '—' }}</span></li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between"><span class="text-white-50">Ville</span><span>{{ $company->city ?? '—' }}</span></li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between"><span class="text-white-50">Abonnement</span><span>{{ $company->subscription ?? '—' }}</span></li>
                    <li class="list-group-item bg-transparent text-white d-flex justify-content-between"><span class="text-white-50">Clients</span><span>{{ $company->clients_count }}</span></li>
                </ul>
            </div>

            <div class="card border-secondary text-bg-dark">
                <div class="card-header">Modules ({{ $modules->where('active', true)->count() }} actifs)</div>
                <div class="card-body d-flex flex-wrap gap-1">
                    @forelse ($modules as $m)
                        <span class="badge {{ $m->active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $m->module_key }}</span>
                    @empty
                        <span class="text-white-50 small">Aucun module.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-secondary text-bg-dark mb-3">
                <div class="card-header">Utilisateurs ({{ $company->users_count }})</div>
                <div class="table-responsive">
                    <table class="table table-dark table-sm mb-0 align-middle">
                        <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Vu</th></tr></thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }} @if($user->id === $company->owner_id)<i class="bi bi-star-fill text-warning" title="Propriétaire"></i>@endif</td>
                                    <td class="small text-white-50">{{ $user->email }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $user->role }}</span></td>
                                    <td class="small text-white-50">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'jamais' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-secondary text-bg-dark">
                <div class="card-header">Audit récent</div>
                <ul class="list-group list-group-flush">
                    @forelse ($audits as $log)
                        <li class="list-group-item bg-transparent text-white small d-flex justify-content-between">
                            <span><code class="text-info">{{ $log->action }}</code> {{ $log->description }}</span>
                            <span class="text-white-50">{{ $log->created_at?->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent text-white-50 small">Aucune action enregistrée pour ce tenant.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
