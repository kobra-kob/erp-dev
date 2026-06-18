@extends('layouts.app')
@section('title', 'Documents')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-folder-fill me-2" style="color:#0d9488"></i>Documents</h1>
            <p class="text-muted mb-0">{{ $documents->total() }} document(s).</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#uploadForm">
            <i class="bi bi-upload me-1"></i>Ajouter un document
        </button>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="collapse mb-3 {{ $errors->any() ? 'show' : '' }}" id="uploadForm">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Catégorie</label>
                        <select name="category" class="form-select">
                            @foreach(\App\Models\Document::CATEGORIES as $key => $label)
                                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client (optionnel)</label>
                        <select name="client_id" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100"><i class="bi bi-check-lg"></i></button>
                    </div>
                    <div class="col-12">
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
                        <div class="form-text">Images, PDF, Word, Excel — max 10 Mo.</div>
                    </div>
                </div>
            </form>
        </div></div>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('documents.index') }}" class="btn btn-sm {{ !$category ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        @foreach(\App\Models\Document::CATEGORIES as $key => $label)
            <a href="{{ route('documents.index', ['category' => $key]) }}"
               class="btn btn-sm {{ $category === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Document</th><th>Catégorie</th><th>Client</th><th>Taille</th><th>Ajouté</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php($previewable = $doc->isImage() || str_contains((string) $doc->mime, 'pdf'))
                        <tr>
                            <td>
                                <i class="bi {{ $doc->isImage() ? 'bi-image' : 'bi-file-earmark' }} me-1 text-muted"></i>
                                <a href="#" class="text-decoration-none fw-semibold"
                                   data-viewer-url="{{ route('documents.show', $doc) }}"
                                   data-viewer-download="{{ route('documents.download', $doc) }}"
                                   data-viewer-name="{{ $doc->original_name }}"
                                   data-viewer-previewable="{{ $previewable ? '1' : '0' }}">{{ $doc->title }}</a>
                                <div class="text-muted small">{{ $doc->original_name }}</div>
                            </td>
                            <td><span class="badge text-bg-light text-dark">{{ $doc->categoryLabel() }}</span></td>
                            <td>{{ $doc->client?->name ?: '—' }}</td>
                            <td class="text-muted">{{ $doc->humanSize() }}</td>
                            <td class="text-muted small">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td class="text-end" style="white-space:nowrap;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Aperçu"
                                        data-viewer-url="{{ route('documents.show', $doc) }}"
                                        data-viewer-download="{{ route('documents.download', $doc) }}"
                                        data-viewer-name="{{ $doc->original_name }}"
                                        data-viewer-previewable="{{ $previewable ? '1' : '0' }}"><i class="bi bi-eye"></i></button>
                                <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                                <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="d-inline"
                                      onsubmit="return confirm('Supprimer ce document ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-folder2-open fs-1 d-block mb-2"></i>Aucun document.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $documents->links() }}</div>
@endsection
