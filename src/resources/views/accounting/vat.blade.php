@extends('layouts.app')
@section('title', 'Déclaration de TVA')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Déclaration de TVA</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Déclaration de TVA</h1>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr><td class="text-muted">Chiffre d'affaires HT (707)</td><td class="text-end">@eur($caHt)</td></tr>
                            <tr><td>TVA collectée <span class="text-muted small">(445710)</span></td><td class="text-end fw-semibold">@eur($collectee)</td></tr>
                            <tr><td>TVA déductible <span class="text-muted small">(445660)</span></td><td class="text-end fw-semibold">− @eur($deductible)</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-top fs-5 fw-bold">
                                <td>{{ $net >= 0 ? 'TVA à décaisser' : 'Crédit de TVA' }}</td>
                                <td class="text-end {{ $net >= 0 ? 'text-danger' : 'text-success' }}">@eur(abs($net))</td>
                            </tr>
                        </tfoot>
                    </table>
                    <p class="text-muted small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>Calcul indicatif sur l'ensemble des écritures (régime réel simplifié).
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
