@extends('layouts.app')
@section('title', 'Compte de résultat')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Compte de résultat</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Compte de résultat</h1>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Charges</h2>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($charges as $c)
                            <tr><td class="font-monospace text-muted">{{ $c['account']->code }}</td><td>{{ $c['account']->name }}</td><td class="text-end">@eur($c['amount'])</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucune charge.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr class="fw-bold border-top"><td colspan="2">Total charges</td><td class="text-end">@eur($totalCharges)</td></tr></tfoot>
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Produits</h2>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($produits as $p)
                            <tr><td class="font-monospace text-muted">{{ $p['account']->code }}</td><td>{{ $p['account']->name }}</td><td class="text-end">@eur($p['amount'])</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucun produit.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr class="fw-bold border-top"><td colspan="2">Total produits</td><td class="text-end">@eur($totalProduits)</td></tr></tfoot>
                </table>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <span class="h5 mb-0">Résultat de l'exercice</span>
            <span class="h4 mb-0 fw-bold {{ $resultat >= 0 ? 'text-success' : 'text-danger' }}">
                @eur($resultat) {{ $resultat >= 0 ? '(bénéfice)' : '(perte)' }}
            </span>
        </div>
    </div>
@endsection
