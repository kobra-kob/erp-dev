@extends('layouts.app')
@section('title', 'Modifier la dépense')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none">Dépenses</a></li>
            <li class="breadcrumb-item active">{{ $expense->label }}</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier la dépense</h1>

    <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('expenses._form')
        <div class="mt-3 d-flex gap-2 align-items-center">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-light">Annuler</a>
            @if($expense->hasReceipt())
                <button type="button" class="btn btn-outline-secondary ms-auto"
                        data-viewer-url="{{ route('expenses.receipt', $expense) }}"
                        data-viewer-download="{{ route('expenses.receipt.download', $expense) }}"
                        data-viewer-name="{{ $expense->receipt_name }}" data-viewer-previewable="1">
                    <i class="bi bi-eye me-1"></i>Voir le justificatif
                </button>
            @endif
        </div>
    </form>

    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="mt-3"
          onsubmit="return confirm('Supprimer cette dépense ?');">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
    </form>
@endsection
