@extends('layouts.public')
@section('title', 'Devis ' . $quote->number)

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
                <div>
                    <h1 class="h4 fw-bold mb-1">Devis {{ $quote->number }}</h1>
                    @if($quote->title)<p class="text-muted mb-0">{{ $quote->title }}</p>@endif
                </div>
                <span class="badge text-bg-{{ $quote->statusColor() }} fs-6 align-self-center">{{ $quote->statusLabel() }}</span>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="text-muted small text-uppercase">Émetteur</div>
                    <div class="fw-semibold">{{ $quote->company?->name }}</div>
                    <div class="text-muted small">{{ $quote->company?->email }}</div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="text-muted small text-uppercase">Destinataire</div>
                    <div class="fw-semibold">{{ $quote->client?->name }}</div>
                    <div class="text-muted small">
                        Émis le {{ $quote->issue_date->format('d/m/Y') }}
                        @if($quote->valid_until)· valable jusqu'au {{ $quote->valid_until->format('d/m/Y') }}@endif
                    </div>
                </div>
            </div>

            @include('partials.document-lines', ['document' => $quote])

            @if($quote->notes)
                <div class="mt-3"><div class="text-muted small text-uppercase">Notes</div><p class="mb-0">{{ $quote->notes }}</p></div>
            @endif

            <hr class="my-4">

            @if($quote->awaitingClient())
                <div class="text-center">
                    <p class="mb-3">Souhaitez-vous accepter ce devis ?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('quotes.public.accept', $quote->public_token) }}"
                              onsubmit="return confirm('Confirmer l\'acceptation de ce devis ?');">
                            @csrf
                            <button class="btn btn-success btn-lg"><i class="bi bi-check-lg me-1"></i>Accepter le devis</button>
                        </form>
                        <form method="POST" action="{{ route('quotes.public.refuse', $quote->public_token) }}"
                              onsubmit="return confirm('Confirmer le refus de ce devis ?');">
                            @csrf
                            <button class="btn btn-outline-danger btn-lg"><i class="bi bi-x-lg me-1"></i>Refuser</button>
                        </form>
                    </div>
                </div>
            @elseif($quote->status === 'accepted')
                <div class="alert alert-success mb-0 text-center"><i class="bi bi-check-circle me-1"></i>Vous avez accepté ce devis. Merci !</div>
            @elseif($quote->status === 'refused')
                <div class="alert alert-secondary mb-0 text-center"><i class="bi bi-x-circle me-1"></i>Ce devis a été refusé.</div>
            @else
                <div class="alert alert-warning mb-0 text-center">Ce devis n'est plus disponible à la validation ({{ $quote->statusLabel() }}).</div>
            @endif
        </div>
    </div>
@endsection
