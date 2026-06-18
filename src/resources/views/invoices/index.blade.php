@extends('layouts.app')
@section('title', 'Factures')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-receipt text-success me-2"></i>Factures</h1>
            <p class="text-muted mb-0">{{ $invoices->total() }} facture(s).</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>Export comptable
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('exports.invoices', ['year' => now()->year]) }}"><i class="bi bi-filetype-csv me-2"></i>Factures {{ now()->year }} (CSV)</a></li>
                    <li><a class="dropdown-item" href="{{ route('exports.payments', ['year' => now()->year]) }}"><i class="bi bi-filetype-csv me-2"></i>Règlements {{ now()->year }} (CSV)</a></li>
                </ul>
            </div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle facture</a>
        </div>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('invoices.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-secondary' }}">Toutes</a>
        @foreach(\App\Models\Invoice::STATUSES as $key => $label)
            <a href="{{ route('invoices.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Numéro</th><th>Client</th><th>Échéance</th>
                        <th class="text-end">Total TTC</th><th class="text-end">Restant dû</th><th>Statut</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->number }}</a></td>
                            <td>{{ $invoice->client->name }}</td>
                            <td class="{{ $invoice->isOverdue() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ optional($invoice->due_date)->format('d/m/Y') ?: '—' }}
                                @if($invoice->isOverdue())<i class="bi bi-exclamation-triangle ms-1" title="En retard"></i>@endif
                            </td>
                            <td class="text-end fw-semibold">@eur($invoice->total_ttc)</td>
                            <td class="text-end">@eur($invoice->remainingAmount())</td>
                            <td><span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Aperçu PDF"
                                        data-viewer-url="{{ route('invoices.pdf', $invoice) }}"
                                        data-viewer-download="{{ route('invoices.pdf', $invoice) }}"
                                        data-viewer-name="{{ $invoice->number }}.pdf" data-viewer-previewable="1"><i class="bi bi-file-pdf"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucune facture.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
