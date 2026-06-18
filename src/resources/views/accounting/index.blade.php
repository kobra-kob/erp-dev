@extends('layouts.app')
@section('title', 'Comptabilité')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-calculator-fill me-2" style="color:#1e3a8a"></i>Comptabilité</h1>
            <p class="text-muted mb-0">{{ $entriesCount }} écriture(s) · {{ $accountsCount }} compte(s) au plan comptable.</p>
        </div>
        <form method="POST" action="{{ route('accounting.rebuild') }}">
            @csrf
            <button class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Recalculer les écritures</button>
        </form>
    </div>

    <div class="alert alert-info d-flex align-items-start">
        <i class="bi bi-info-circle me-2 mt-1"></i>
        <div>Les écritures sont générées automatiquement depuis vos <strong>factures</strong>, <strong>paiements</strong> et <strong>dépenses</strong>.
        Cliquez sur « Recalculer » après avoir saisi de nouveaux documents.</div>
    </div>

    <div class="row g-3 mb-4">
        @php($cards = [
            ['Produits', $produits, '#059669', 'bi-graph-up-arrow'],
            ['Charges', $charges, '#dc2626', 'bi-graph-down-arrow'],
            ['Résultat', $resultat, $resultat >= 0 ? '#2563eb' : '#dc2626', 'bi-bar-chart-line'],
            ['Trésorerie (512)', $tresorerie, '#0891b2', 'bi-bank'],
        ])
        @foreach($cards as [$label, $val, $color, $icon])
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:{{ $color }}"><i class="bi {{ $icon }} fs-5"></i></div>
                    <div><div class="text-muted small text-uppercase">{{ $label }}</div><div class="h5 fw-bold mb-0">@eur($val)</div></div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        @php($links = [
            ['accounting.accounts', 'bi-list-columns', 'Plan comptable', 'Tous les comptes (PCG)'],
            ['accounting.journal', 'bi-journal-text', 'Journal', 'Toutes les écritures'],
            ['accounting.ledger', 'bi-book', 'Grand livre', 'Détail par compte'],
            ['accounting.balance', 'bi-scale', 'Balance', 'Débit / crédit / solde'],
            ['accounting.income', 'bi-clipboard-data', 'Compte de résultat', 'Produits − charges'],
            ['accounting.balance-sheet', 'bi-layout-split', 'Bilan', 'Actif / passif (simplifié)'],
            ['accounting.vat', 'bi-percent', 'Déclaration de TVA', 'Collectée − déductible'],
            ['bank.index', 'bi-bank2', 'Banque', 'Relevés & rapprochement'],
        ])
        @foreach($links as [$route, $icon, $title, $desc])
            <div class="col-md-6 col-lg-4">
                <a href="{{ route($route) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi {{ $icon }} fs-3 text-primary"></i>
                        <div><div class="fw-semibold">{{ $title }}</div><div class="text-muted small">{{ $desc }}</div></div>
                    </div>
                </a>
            </div>
        @endforeach
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('accounting.fec') }}" class="card border-0 shadow-sm h-100 text-decoration-none text-dark">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi bi-file-earmark-arrow-down fs-3 text-success"></i>
                    <div><div class="fw-semibold">Export FEC</div><div class="text-muted small">Fichier des Écritures Comptables (légal)</div></div>
                </div>
            </a>
        </div>
    </div>
@endsection
