@extends('layouts.app')
@section('title', $account->name)

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item"><a href="{{ route('bank.index') }}" class="text-decoration-none">Banque</a></li>
        <li class="breadcrumb-item active">{{ $account->name }}</li>
    </ol></nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $account->name }}</h1>
            <p class="text-muted mb-0">{{ $account->bank_name }} · {{ $account->iban ?: '—' }}</p>
        </div>
        <div class="text-end">
            <div class="text-muted small text-uppercase">Solde</div>
            <div class="h3 fw-bold mb-0">@eur($account->balance())</div>
        </div>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-2 mb-3">
        <div class="col-md-8">
            <form method="POST" action="{{ route('bank.import', $account) }}" enctype="multipart/form-data" class="card border-0 shadow-sm h-100">
                @csrf
                <div class="card-body d-flex align-items-end gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label small mb-1">Importer un relevé CSV (<code>date;libellé;montant</code>)</label>
                        <input type="file" name="statement" accept=".csv,.txt" class="form-control form-control-sm" required>
                    </div>
                    <button class="btn btn-sm btn-primary"><i class="bi bi-upload me-1"></i>Importer</button>
                </div>
            </form>
        </div>
        <div class="col-md-4">
            <form method="POST" action="{{ route('bank.reconcile', $account) }}" class="card border-0 shadow-sm h-100">
                @csrf
                <div class="card-body d-flex align-items-center justify-content-between">
                    <span class="small text-muted">Rapprochement automatique<br>(crédits ↔ paiements)</span>
                    <button class="btn btn-sm btn-success"><i class="bi bi-link-45deg"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Libellé</th><th class="text-end">Montant</th><th>Rapproché</th><th></th></tr></thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr class="{{ $tx->reconciled ? '' : 'table-warning' }}">
                            <td class="text-muted">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td>{{ $tx->label }}
                                @if($tx->payment_id)<span class="text-muted small">· lettré paiement #{{ $tx->payment_id }}</span>@endif
                            </td>
                            <td class="text-end fw-semibold {{ $tx->amount < 0 ? 'text-danger' : 'text-success' }}">@eur($tx->amount)</td>
                            <td>@if($tx->reconciled)<span class="badge text-bg-success">Oui</span>@else<span class="badge text-bg-secondary">Non</span>@endif</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('bank.toggle', [$account, $tx]) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary" title="Basculer le rapprochement">
                                        <i class="bi {{ $tx->reconciled ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-bank fs-1 d-block mb-2"></i>Aucune opération. Importez un relevé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $transactions->links() }}</div>
@endsection
