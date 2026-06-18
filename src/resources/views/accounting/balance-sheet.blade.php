@extends('layouts.app')
@section('title', 'Bilan')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Bilan</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-1">Bilan simplifié</h1>
    <p class="text-muted mb-4">Vue synthétique (hors immobilisations et capital).</p>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Actif</h2>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($actif as $l)
                            <tr><td>{{ $l['label'] }}</td><td class="text-end">@eur($l['amount'])</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">—</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr class="fw-bold border-top"><td>Total actif</td><td class="text-end">@eur($totalActif)</td></tr></tfoot>
                </table>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Passif</h2>
                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($passif as $l)
                            <tr><td>{{ $l['label'] }}</td><td class="text-end">@eur($l['amount'])</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-muted">—</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot><tr class="fw-bold border-top"><td>Total passif</td><td class="text-end">@eur($totalPassif)</td></tr></tfoot>
                </table>
            </div></div>
        </div>
    </div>
@endsection
