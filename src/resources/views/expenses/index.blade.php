@extends('layouts.app')
@section('title', 'Dépenses')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-cash-coin text-danger me-2"></i>Dépenses</h1>
            <p class="text-muted mb-0">{{ $expenses->total() }} dépense(s).</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle dépense</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:#dc2626"><i class="bi bi-calendar-month fs-5"></i></div>
                <div><div class="text-muted small text-uppercase">Dépenses du mois</div><div class="h4 fw-bold mb-0">@eur($monthTotal)</div></div>
            </div></div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:#9333ea"><i class="bi bi-calendar3 fs-5"></i></div>
                <div><div class="text-muted small text-uppercase">Dépenses {{ now()->year }}</div><div class="h4 fw-bold mb-0">@eur($yearTotal)</div></div>
            </div></div>
        </div>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('expenses.index') }}" class="btn btn-sm {{ !$category ? 'btn-dark' : 'btn-outline-secondary' }}">Toutes</a>
        @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
            <a href="{{ route('expenses.index', ['category' => $key]) }}"
               class="btn btn-sm {{ $category === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Libellé</th><th>Catégorie</th><th>Fournisseur</th><th class="text-end">Montant</th><th>Justif.</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="text-muted">{{ $expense->spent_at->format('d/m/Y') }}</td>
                            <td class="fw-semibold">{{ $expense->label }}</td>
                            <td><span class="badge text-bg-light text-dark">{{ $expense->categoryLabel() }}</span></td>
                            <td>{{ $expense->supplier ?: '—' }}</td>
                            <td class="text-end fw-semibold">@eur($expense->amount)</td>
                            <td>
                                @if($expense->hasReceipt())
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-viewer-url="{{ route('expenses.receipt', $expense) }}"
                                            data-viewer-download="{{ route('expenses.receipt.download', $expense) }}"
                                            data-viewer-name="{{ $expense->receipt_name }}"
                                            data-viewer-previewable="1" title="Voir le justificatif">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucune dépense.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $expenses->links() }}</div>
@endsection
