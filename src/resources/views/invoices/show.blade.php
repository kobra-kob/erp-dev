@extends('layouts.app')
@section('title', $invoice->number)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-decoration-none">Factures</a></li>
            <li class="breadcrumb-item active">{{ $invoice->number }}</li>
        </ol>
    </nav>

    @if($errors->any())
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $invoice->number }}
                <span class="badge text-bg-{{ $invoice->statusColor() }} align-middle">{{ $invoice->statusLabel() }}</span>
                @if($invoice->isOverdue())<span class="badge text-bg-danger align-middle">En retard</span>@endif
            </h1>
            <p class="text-muted mb-0">
                {{ $invoice->title }}
                @if($invoice->quote)· issue du devis <a href="{{ route('quotes.show', $invoice->quote) }}" class="text-decoration-none">{{ $invoice->quote->number }}</a>@endif
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary"
                    data-viewer-url="{{ route('invoices.pdf', $invoice) }}"
                    data-viewer-download="{{ route('invoices.pdf', $invoice) }}"
                    data-viewer-name="{{ $invoice->number }}.pdf" data-viewer-previewable="1"><i class="bi bi-file-pdf me-1"></i>PDF</button>
            <form method="POST" action="{{ route('invoices.send', $invoice) }}"
                  onsubmit="return confirm('Envoyer cette facture par e-mail au client ?');">
                @csrf
                <button class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Envoyer</button>
            </form>
            @if($invoice->status !== 'paid')
                <form method="POST" action="{{ route('invoices.remind', $invoice) }}">
                    @csrf
                    <button class="btn btn-outline-warning"><i class="bi bi-envelope-exclamation me-1"></i>Relancer</button>
                </form>
            @endif
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    @include('partials.document-lines', ['document' => $invoice])
                </div>
            </div>

            {{-- Paiements --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Paiements</h2>
                    <table class="table table-sm align-middle">
                        <thead class="table-light"><tr><th>Date</th><th>Moyen</th><th>Note</th><th class="text-end">Montant</th><th></th></tr></thead>
                        <tbody>
                            @forelse($invoice->payments as $p)
                                <tr>
                                    <td>{{ $p->paid_at->format('d/m/Y') }}</td>
                                    <td>{{ $p->methodLabel() }}</td>
                                    <td class="text-muted small">{{ $p->note }}</td>
                                    <td class="text-end fw-semibold">@eur($p->amount)</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('invoices.payments.destroy', [$invoice, $p]) }}" onsubmit="return confirm('Supprimer ce paiement ?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Aucun paiement enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($invoice->remainingAmount() > 0)
                        <hr>
                        <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-sm-3">
                                <label class="form-label small">Montant (€)</label>
                                <input type="number" step="0.01" min="0.01" name="amount" value="{{ $invoice->remainingAmount() }}"
                                       class="form-control form-control-sm @error('amount') is-invalid @enderror" required>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label small">Date</label>
                                <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label small">Moyen</label>
                                <select name="method" class="form-select form-select-sm">
                                    @foreach(\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button class="btn btn-sm btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Encaisser</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Règlement</h2>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Total TTC</span><strong>@eur($invoice->total_ttc)</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Payé</span><span class="text-success">@eur($invoice->paid_amount)</span></div>
                    <div class="d-flex justify-content-between border-top pt-2"><span class="fw-bold">Restant dû</span><span class="fw-bold fs-5">@eur($invoice->remainingAmount())</span></div>
                    @php($pct = (float)$invoice->total_ttc > 0 ? min(100, round((float)$invoice->paid_amount / (float)$invoice->total_ttc * 100)) : 0)
                    <div class="progress mt-3" style="height:8px;">
                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="small text-muted text-center mt-1">{{ $pct }} % réglé</div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Informations</h2>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Client</dt>
                        <dd class="col-7"><a href="{{ route('clients.show', $invoice->client) }}" class="text-decoration-none">{{ $invoice->client->name }}</a></dd>
                        <dt class="col-5 text-muted fw-normal">Émise le</dt>
                        <dd class="col-7">{{ $invoice->issue_date->format('d/m/Y') }}</dd>
                        <dt class="col-5 text-muted fw-normal">Échéance</dt>
                        <dd class="col-7 {{ $invoice->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ optional($invoice->due_date)->format('d/m/Y') ?: '—' }}</dd>
                        @if($invoice->sent_at)
                            <dt class="col-5 text-muted fw-normal">Envoyée le</dt>
                            <dd class="col-7">{{ $invoice->sent_at->format('d/m/Y') }}</dd>
                        @endif
                        @if($invoice->reminders_sent > 0)
                            <dt class="col-5 text-muted fw-normal">Relances</dt>
                            <dd class="col-7">{{ $invoice->reminders_sent }} (dernière : {{ optional($invoice->last_reminder_at)->format('d/m/Y') }})</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="mt-3"
                  onsubmit="return confirm('Supprimer cette facture ?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
        </div>
    </div>
@endsection
