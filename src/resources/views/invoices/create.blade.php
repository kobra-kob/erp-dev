@extends('layouts.app')
@section('title', 'Nouvelle facture')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none">Factures</a></li>
            <li class="breadcrumb-item active">Nouvelle</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Nouvelle facture</h1>

    <form method="POST" action="{{ route('invoices.store') }}">
        @include('invoices._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Créer la facture</button>
            <a href="{{ route('invoices.index') }}" class="btn btn-light">Annuler</a>
        </div>
    </form>
@endsection
