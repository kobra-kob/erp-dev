@extends('layouts.app')
@section('title', $employee->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none">Employés</a></li>
            <li class="breadcrumb-item active">{{ $employee->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;font-size:1.5rem;">{{ strtoupper(substr($employee->name,0,1)) }}</div>
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $employee->name }}</h1>
                <span class="badge text-bg-primary">{{ $employee->roleLabel() }}</span>
                @if($employee->is_active)<span class="badge text-bg-success">Actif</span>@else<span class="badge text-bg-secondary">Inactif</span>@endif
            </div>
        </div>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Coordonnées</h2>
                <dl class="row mb-0">
                    <dt class="col-5 text-muted fw-normal">E-mail</dt><dd class="col-7">{{ $employee->email }}</dd>
                    <dt class="col-5 text-muted fw-normal">Téléphone</dt><dd class="col-7">{{ $employee->phone ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Compétence</dt><dd class="col-7">{{ $employee->skill ?: '—' }}</dd>
                </dl>
            </div></div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Contrats &amp; documents RH</h2>

                @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

                <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small">Intitulé</label>
                        <input type="text" name="title" class="form-control form-control-sm" placeholder="Contrat CDI 2026" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Type</label>
                        <select name="type" class="form-select form-select-sm">
                            @foreach(\App\Models\EmployeeDocument::TYPES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Fichier (PDF/Word/image)</label>
                        <input type="file" name="file" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-sm btn-primary"><i class="bi bi-upload me-1"></i>Ajouter</button>
                    </div>
                </form>

                @forelse($employee->documents as $doc)
                    @php($previewable = $doc->isImage() || str_contains((string) $doc->mime, 'pdf'))
                    <div class="d-flex align-items-center gap-2 border-bottom py-2">
                        <i class="bi {{ $doc->isImage() ? 'bi-image' : 'bi-file-earmark-text' }} fs-5 text-muted"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">{{ $doc->title }}</div>
                            <span class="badge text-bg-light text-dark">{{ $doc->typeLabel() }}</span>
                            <span class="text-muted small">{{ $doc->humanSize() }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-viewer-url="{{ route('employees.documents.show', [$employee, $doc]) }}"
                                data-viewer-download="{{ route('employees.documents.download', [$employee, $doc]) }}"
                                data-viewer-name="{{ $doc->original_name }}"
                                data-viewer-previewable="{{ $previewable ? '1' : '0' }}" title="Consulter">
                            <i class="bi bi-eye"></i>
                        </button>
                        <form method="POST" action="{{ route('employees.documents.destroy', [$employee, $doc]) }}"
                              onsubmit="return confirm('Supprimer ce document ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Aucun contrat ou document pour cet employé.</p>
                @endforelse
            </div></div>
        </div>
    </div>
@endsection
