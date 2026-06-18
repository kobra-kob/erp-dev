@extends('layouts.app')
@section('title', $client->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-decoration-none">Clients</a></li>
            <li class="breadcrumb-item active">{{ $client->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;font-size:1.5rem;">{{ strtoupper(substr($client->name,0,1)) }}</div>
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $client->name }}</h1>
                <span class="badge {{ $client->type === 'professionnel' ? 'text-bg-info' : 'text-bg-secondary' }}">{{ $client->typeLabel() }}</span>
            </div>
        </div>
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Modifier</a>
    </div>

    <div class="row g-3">
        {{-- Coordonnées --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Coordonnées</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">Interlocuteur</dt>
                        <dd class="col-sm-8">{{ $client->contact_name ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Téléphone</dt>
                        <dd class="col-sm-8">{{ $client->phone ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">E-mail</dt>
                        <dd class="col-sm-8">{{ $client->email ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">Adresse</dt>
                        <dd class="col-sm-8">{{ $client->fullAddress() ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted fw-normal">SIRET</dt>
                        <dd class="col-sm-8">{{ $client->siret ?: '—' }}</dd>
                    </dl>
                    @if($client->notes)
                        <hr>
                        <h2 class="h6 text-uppercase text-muted mb-2">Notes</h2>
                        <p class="mb-0">{{ $client->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Historique --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Historique</h2>
                    <div class="row text-center g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="h4 fw-bold mb-0">{{ $client->quotesCount() }}</div>
                                <div class="small text-muted">Devis</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 py-3">
                                <div class="h4 fw-bold mb-0">{{ $client->invoicesCount() }}</div>
                                <div class="small text-muted">Factures</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Dernier contact</span>
                        <strong>{{ optional($client->last_contact_at)->format('d/m/Y') ?: '—' }}</strong>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="{{ route('quotes.create') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau devis</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Devis & factures du client --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Devis récents</h2>
                    @forelse($client->quotes()->limit(5)->get() as $quote)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <a href="{{ route('quotes.show', $quote) }}" class="text-decoration-none">{{ $quote->number }}</a>
                            <span><span class="badge text-bg-{{ $quote->statusColor() }}">{{ $quote->statusLabel() }}</span> <strong class="ms-1">@eur($quote->total_ttc)</strong></span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucun devis.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Factures récentes</h2>
                    @forelse($client->invoices()->limit(5)->get() as $invoice)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">{{ $invoice->number }}</a>
                            <span><span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span> <strong class="ms-1">@eur($invoice->total_ttc)</strong></span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune facture.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
