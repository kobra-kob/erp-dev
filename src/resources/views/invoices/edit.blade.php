@extends('layouts.app')
@section('title', 'Modifier la facture')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none">Factures</a></li>
            <li class="breadcrumb-item"><a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">{{ $invoice->number }}</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Modifier {{ $invoice->number }}</h1>

    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @method('PUT')
        @include('invoices._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
