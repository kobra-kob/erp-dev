@extends('layouts.app')
@section('title', 'Journal des écritures')

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('accounting.index') }}" class="text-decoration-none">Comptabilité</a></li>
        <li class="breadcrumb-item active">Journal</li>
    </ol></nav>

    <h1 class="h3 fw-bold mb-3">Journal des écritures</h1>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('accounting.journal') }}" class="btn btn-sm {{ !$current ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        @foreach($journals as $j)
            <a href="{{ route('accounting.journal', ['journal' => $j->code]) }}"
               class="btn btn-sm {{ $current === $j->code ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $j->code }} — {{ $j->name }}</a>
        @endforeach
    </div>

    @forelse($entries as $entry)
        <div class="card border-0 shadow-sm mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between">
                    <div><span class="badge text-bg-secondary">{{ $entry->journal->code }}</span>
                        <strong class="ms-1">{{ $entry->label }}</strong>
                        @if($entry->reference)<span class="text-muted">· {{ $entry->reference }}</span>@endif
                    </div>
                    <span class="text-muted small">{{ $entry->entry_date->format('d/m/Y') }}
                        @unless($entry->isBalanced())<span class="badge text-bg-danger ms-1">déséquilibrée</span>@endunless
                    </span>
                </div>
                <table class="table table-sm mb-0 mt-2">
                    <tbody>
                        @foreach($entry->lines as $line)
                            <tr>
                                <td class="font-monospace text-muted" style="width:120px;">{{ $line->account->code }}</td>
                                <td>{{ $line->account->name }} <span class="text-muted small">{{ $line->label }}</span></td>
                                <td class="text-end" style="width:120px;">@if($line->debit > 0)@eur($line->debit)@endif</td>
                                <td class="text-end" style="width:120px;">@if($line->credit > 0)@eur($line->credit)@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
            <i class="bi bi-journal fs-1 d-block mb-2"></i>Aucune écriture. Lancez « Recalculer les écritures ».
        </div></div>
    @endforelse

    <div class="mt-3">{{ $entries->links() }}</div>
@endsection
