@extends('layouts.app')
@section('title', $intervention->title)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('interventions.index') }}" class="text-decoration-none">Planning</a></li>
            <li class="breadcrumb-item active">{{ $intervention->title }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <h1 class="h3 fw-bold mb-0">{{ $intervention->title }}
            <span class="badge text-bg-secondary align-middle">{{ $intervention->statusLabel() }}</span>
        </h1>
        <a href="{{ route('interventions.edit', $intervention) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <dl class="row mb-0">
                <dt class="col-sm-3 text-muted fw-normal">Date</dt>
                <dd class="col-sm-9">{{ $intervention->start_at->translatedFormat('l d F Y') }} · {{ $intervention->start_at->format('H:i') }} → {{ $intervention->end_at->format('H:i') }} <span class="text-muted">({{ $intervention->duration() }})</span></dd>

                <dt class="col-sm-3 text-muted fw-normal">Technicien</dt>
                <dd class="col-sm-9">{{ $intervention->technician?->name ?? '— Non assigné —' }}</dd>

                <dt class="col-sm-3 text-muted fw-normal">Client</dt>
                <dd class="col-sm-9">
                    @if($intervention->client)<a href="{{ route('clients.show', $intervention->client) }}" class="text-decoration-none">{{ $intervention->client->name }}</a>@else — @endif
                </dd>

                <dt class="col-sm-3 text-muted fw-normal">Chantier</dt>
                <dd class="col-sm-9">
                    @if($intervention->project)<a href="{{ route('projects.show', $intervention->project) }}" class="text-decoration-none">{{ $intervention->project->name }}</a>@else — @endif
                </dd>

                <dt class="col-sm-3 text-muted fw-normal">Adresse</dt>
                <dd class="col-sm-9">{{ $intervention->address ?: '—' }}</dd>

                @if($intervention->notes)
                    <dt class="col-sm-3 text-muted fw-normal">Notes</dt>
                    <dd class="col-sm-9">{{ $intervention->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>
@endsection
