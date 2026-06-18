@extends('layouts.app')
@section('title', 'Devis')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Devis</h1>
            <p class="text-muted mb-0">{{ $quotes->total() }} devis.</p>
        </div>
        <a href="{{ route('quotes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau devis</a>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('quotes.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        @foreach(\App\Models\Quote::STATUSES as $key => $label)
            <a href="{{ route('quotes.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Numéro</th><th>Client</th><th>Date</th>
                        <th class="text-end">Total TTC</th><th>Statut</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                        <tr>
                            <td><a href="{{ route('quotes.show', $quote) }}" class="fw-semibold text-decoration-none">{{ $quote->number }}</a></td>
                            <td>{{ $quote->client->name }}</td>
                            <td class="text-muted">{{ $quote->issue_date->format('d/m/Y') }}</td>
                            <td class="text-end fw-semibold">@eur($quote->total_ttc)</td>
                            <td><span class="badge text-bg-{{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Aperçu PDF"
                                        data-viewer-url="{{ route('quotes.pdf', $quote) }}"
                                        data-viewer-download="{{ route('quotes.pdf', $quote) }}"
                                        data-viewer-name="{{ $quote->number }}.pdf" data-viewer-previewable="1"><i class="bi bi-file-pdf"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucun devis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $quotes->links() }}</div>
@endsection
