@extends('layouts.app')
@section('title', 'Banque')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Banque</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-3"><i class="bi bi-bank2 me-2"></i>Banque</h1>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="alert alert-secondary d-flex align-items-start small">
        <i class="bi bi-shield-lock me-2 mt-1"></i>
        <div>La connexion bancaire temps réel (agrégation DSP2) n'est pas activée : importez vos relevés au format CSV
        (<code>date;libellé;montant</code>) pour effectuer le rapprochement avec vos paiements.</div>
    </div>

    <div class="row g-3">
        @forelse($accounts as $account)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('bank.show', $account) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h2 class="h6 fw-bold mb-1">{{ $account->name }}</h2>
                            @if($account->unreconciled_count > 0)<span class="badge text-bg-warning">{{ $account->unreconciled_count }} à pointer</span>@endif
                        </div>
                        <div class="text-muted small">{{ $account->bank_name }} · {{ $account->iban ?: '—' }}</div>
                        <div class="h4 fw-bold mt-2 mb-0">@eur($account->balance())</div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-muted">Aucun compte bancaire. Ajoutez-en un ci-dessous.</div></div></div>
        @endforelse
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted mb-3">Ajouter un compte bancaire</h2>
            <form method="POST" action="{{ route('bank.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3"><label class="form-label small">Nom</label><input type="text" name="name" class="form-control form-control-sm" required></div>
                <div class="col-md-3"><label class="form-label small">Banque</label><input type="text" name="bank_name" class="form-control form-control-sm"></div>
                <div class="col-md-3"><label class="form-label small">IBAN</label><input type="text" name="iban" class="form-control form-control-sm"></div>
                <div class="col-md-2"><label class="form-label small">Solde initial</label><input type="number" step="0.01" name="opening_balance" value="0" class="form-control form-control-sm"></div>
                <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="bi bi-plus-lg"></i></button></div>
            </form>
        </div>
    </div>
@endsection
