@extends('layouts.app')
@section('title', 'Nouvelle demande de congés')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}" class="text-decoration-none">Congés</a></li>
            <li class="breadcrumb-item active">Nouvelle demande</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouvelle demande de congés</h1>

    <form method="POST" action="{{ route('leaves.store') }}">
        @csrf
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach(\App\Models\LeaveRequest::TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $leave->type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Du <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($leave->start_date)->format('Y-m-d')) }}"
                               class="form-control @error('start_date') is-invalid @enderror" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Au <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($leave->end_date)->format('Y-m-d')) }}"
                               class="form-control @error('end_date') is-invalid @enderror" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif (optionnel)</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Précisez si besoin…">{{ old('reason', $leave->reason) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Envoyer la demande</button>
            <a href="{{ route('leaves.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
