@extends('layouts.app')
@section('title', $project->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none">Chantiers</a></li>
            <li class="breadcrumb-item active">{{ $project->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $project->name }}
                <span class="badge text-bg-{{ $project->statusColor() }} align-middle">{{ $project->statusLabel() }}</span>
            </h1>
            <p class="text-muted mb-0">
                @if($project->client)<i class="bi bi-person me-1"></i><a href="{{ route('clients.show', $project->client) }}" class="text-decoration-none">{{ $project->client->name }}</a>@endif
                @if($project->address)<span class="ms-2"><i class="bi bi-geo-alt me-1"></i>{{ $project->address }} {{ $project->city }}</span>@endif
            </p>
        </div>
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            {{-- Avancement --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-1">
                        <h2 class="h6 text-uppercase text-muted mb-0">Avancement</h2>
                        <strong>{{ $project->progress }} %</strong>
                    </div>
                    <div class="progress" style="height:14px;">
                        <div class="progress-bar bg-{{ $project->statusColor() }}" style="width: {{ $project->progress }}%"></div>
                    </div>
                    @if($project->description)<p class="mt-3 mb-0">{{ $project->description }}</p>@endif
                </div>
            </div>

            {{-- Interventions liées --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 text-uppercase text-muted mb-0">Interventions</h2>
                        <a href="{{ route('interventions.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i></a>
                    </div>
                    @forelse($project->interventions as $i)
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <a href="{{ route('interventions.show', $i) }}" class="text-decoration-none">{{ $i->title }}</a>
                            <span class="text-muted small">{{ $i->start_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune intervention planifiée.</p>
                    @endforelse
                </div>
            </div>

            {{-- Commentaires --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Commentaires</h2>
                    <form method="POST" action="{{ route('projects.comments.store', $project) }}" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="body" class="form-control @error('body') is-invalid @enderror" placeholder="Ajouter un commentaire…" required>
                            <button class="btn btn-primary"><i class="bi bi-send"></i></button>
                        </div>
                    </form>
                    @forelse($project->comments as $comment)
                        <div class="d-flex gap-2 mb-3">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:36px;height:36px;">{{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}</div>
                            <div>
                                <div class="small"><strong>{{ $comment->user?->name ?? 'Utilisateur' }}</strong>
                                    <span class="text-muted">· {{ $comment->created_at->diffForHumans() }}</span></div>
                                <div>{{ $comment->body }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucun commentaire.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Infos --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Informations</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Budget</dt>
                        <dd class="col-7">{{ $project->budget ? number_format($project->budget, 2, ',', ' ').' €' : '—' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Début</dt>
                        <dd class="col-7">{{ optional($project->start_date)->format('d/m/Y') ?: '—' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Fin prévue</dt>
                        <dd class="col-7">{{ optional($project->end_date)->format('d/m/Y') ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Pièces jointes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Photos & documents</h2>
                    <form method="POST" action="{{ route('projects.documents.store', $project) }}" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror" required>
                            <button class="btn btn-sm btn-primary"><i class="bi bi-upload"></i></button>
                        </div>
                        @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">Images, PDF, Word, Excel — max 10 Mo.</div>
                    </form>

                    @forelse($project->documents as $doc)
                        @php($previewable = $doc->isImage() || str_contains((string) $doc->mime, 'pdf'))
                        <div class="d-flex align-items-center gap-2 border-bottom py-2">
                            <i class="bi {{ $doc->isImage() ? 'bi-image' : 'bi-file-earmark' }} fs-5 text-muted"></i>
                            <div class="flex-grow-1 min-w-0">
                                <a href="#" class="text-decoration-none d-block text-truncate"
                                   data-viewer-url="{{ route('projects.documents.show', [$project, $doc]) }}"
                                   data-viewer-download="{{ route('projects.documents.download', [$project, $doc]) }}"
                                   data-viewer-name="{{ $doc->original_name }}"
                                   data-viewer-previewable="{{ $previewable ? '1' : '0' }}">{{ $doc->original_name }}</a>
                                <span class="text-muted small">{{ $doc->humanSize() }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Aperçu"
                                    data-viewer-url="{{ route('projects.documents.show', [$project, $doc]) }}"
                                    data-viewer-download="{{ route('projects.documents.download', [$project, $doc]) }}"
                                    data-viewer-name="{{ $doc->original_name }}"
                                    data-viewer-previewable="{{ $previewable ? '1' : '0' }}"><i class="bi bi-eye"></i></button>
                            <form method="POST" action="{{ route('projects.documents.destroy', [$project, $doc]) }}"
                                  onsubmit="return confirm('Supprimer ce fichier ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune pièce jointe.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
