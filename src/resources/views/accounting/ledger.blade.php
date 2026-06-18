@extends('layouts.app')
@section('title', 'Grand livre')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Grand livre</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-3">Grand livre</h1>

    <form method="GET" class="mb-3" style="max-width:480px;">
        <div class="input-group">
            <span class="input-group-text">Compte</span>
            <select name="account" class="form-select" onchange="this.form.submit()">
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}" @selected($account && $account->id === $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($account)
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Écriture</th><th class="text-end">Débit</th><th class="text-end">Crédit</th><th class="text-end">Solde</th></tr></thead>
                    <tbody>
                        @php($solde = 0)
                        @forelse($lines as $line)
                            @php($solde += (float)$line->debit - (float)$line->credit)
                            <tr>
                                <td class="text-muted">{{ $line->entry->entry_date->format('d/m/Y') }}</td>
                                <td>{{ $line->entry->label }} <span class="text-muted small">{{ $line->label }}</span></td>
                                <td class="text-end">@if($line->debit > 0)@eur($line->debit)@endif</td>
                                <td class="text-end">@if($line->credit > 0)@eur($line->credit)@endif</td>
                                <td class="text-end fw-semibold">@eur($solde)</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucun mouvement sur ce compte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
