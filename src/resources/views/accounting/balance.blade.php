@extends('layouts.app')
@section('title', 'Balance')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Balance</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Balance générale</h1>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:120px;">Compte</th><th>Libellé</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        @php($solde = round($row['debit'] - $row['credit'], 2))
                        <tr>
                            <td class="font-monospace text-muted">{{ $row['account']->code }}</td>
                            <td>{{ $row['account']->name }}</td>
                            <td class="text-end">@eur($row['debit'])</td>
                            <td class="text-end">@eur($row['credit'])</td>
                            <td class="text-end fw-semibold {{ $solde < 0 ? 'text-danger' : '' }}">@eur($solde)</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Aucun compte mouvementé.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2">Totaux</td>
                            <td class="text-end">@eur($totalDebit)</td>
                            <td class="text-end">@eur($totalCredit)</td>
                            <td class="text-end">@if(abs($totalDebit - $totalCredit) < 0.01)<span class="text-success">équilibrée ✓</span>@else<span class="text-danger">écart</span>@endif</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
