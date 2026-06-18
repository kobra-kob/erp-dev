@extends('layouts.app')
@section('title', $quote->number)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('quotes.index') }}" class="text-decoration-none">Devis</a></li>
            <li class="breadcrumb-item active">{{ $quote->number }}</li>
        </ol>
    </nav>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $quote->number }}
                <span class="badge text-bg-{{ $quote->statusColor() }} align-middle">{{ $quote->statusLabel() }}</span>
            </h1>
            <p class="text-muted mb-0">{{ $quote->title }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary"
                    data-viewer-url="{{ route('quotes.pdf', $quote) }}"
                    data-viewer-download="{{ route('quotes.pdf', $quote) }}"
                    data-viewer-name="{{ $quote->number }}.pdf" data-viewer-previewable="1"><i class="bi bi-file-pdf me-1"></i>PDF</button>
            <form method="POST" action="{{ route('quotes.send', $quote) }}"
                  onsubmit="return confirm('Envoyer ce devis par e-mail au client ?');">
                @csrf
                <button class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Envoyer</button>
            </form>
            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>

            @if($quote->isConvertedToInvoice())
                @php($inv = \App\Models\Invoice::where('quote_id', $quote->id)->first())
                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-success"><i class="bi bi-receipt me-1"></i>Voir la facture</a>
            @elseif($quote->isAccepted())
                <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                    @csrf
                    <button class="btn btn-success"><i class="bi bi-arrow-right-circle me-1"></i>Transformer en facture</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    @include('partials.document-lines', ['document' => $quote])
                </div>
            </div>
            @if($quote->notes)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 text-uppercase text-muted mb-2">Notes</h2>
                        <p class="mb-0">{{ $quote->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Informations</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Client</dt>
                        <dd class="col-7"><a href="{{ route('clients.show', $quote->client) }}" class="text-decoration-none">{{ $quote->client->name }}</a></dd>
                        <dt class="col-5 text-muted fw-normal">Émis le</dt>
                        <dd class="col-7">{{ $quote->issue_date->format('d/m/Y') }}</dd>
                        <dt class="col-5 text-muted fw-normal">Valable jusqu'au</dt>
                        <dd class="col-7">{{ optional($quote->valid_until)->format('d/m/Y') ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Changer le statut</h2>
                    <form method="POST" action="{{ route('quotes.status', $quote) }}" class="d-flex gap-2">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select form-select-sm">
                            @foreach(\App\Models\Quote::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($quote->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary">OK</button>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('quotes.destroy', $quote) }}" class="mt-3"
                  onsubmit="return confirm('Supprimer ce devis ?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
        </div>
    </div>
@endsection
