@extends('layouts.app')
@section('title', 'Plan comptable')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Plan comptable</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-4">Plan comptable</h1>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:140px;">Compte</th><th>Libellé</th><th>Type</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="fw-semibold font-monospace">{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td><span class="badge text-bg-light text-dark">{{ $account->typeLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-5">Aucun compte. Lancez « Recalculer les écritures » pour générer le plan comptable.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
