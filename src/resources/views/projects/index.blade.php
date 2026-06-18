@extends('layouts.app')
@section('title', 'Chantiers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bricks text-warning me-2"></i>Chantiers</h1>
            <p class="text-muted mb-0">{{ $projects->total() }} chantier(s).</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau chantier</a>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('projects.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        @foreach(\App\Models\Project::STATUSES as $key => $label)
            <a href="{{ route('projects.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="row g-3">
        @forelse($projects as $project)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('projects.show', $project) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="h6 fw-bold mb-0">{{ $project->name }}</h2>
                            <span class="badge text-bg-{{ $project->statusColor() }}">{{ $project->statusLabel() }}</span>
                        </div>
                        <p class="text-muted small mb-2">
                            @if($project->client)<i class="bi bi-person me-1"></i>{{ $project->client->name }}<br>@endif
                            @if($project->city)<i class="bi bi-geo-alt me-1"></i>{{ $project->city }}@endif
                        </p>
                        <div class="progress mb-1" style="height:8px;">
                            <div class="progress-bar bg-{{ $project->statusColor() }}" style="width: {{ $project->progress }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>{{ $project->progress }} %</span>
                            @if($project->budget)<span>@eur($project->budget)</span>@endif
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucun chantier.
                </div></div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $projects->links() }}</div>
@endsection
