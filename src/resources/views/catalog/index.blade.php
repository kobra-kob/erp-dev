@extends('layouts.app')
@section('title', 'Catalogue de prestations')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bricks text-warning me-2"></i>Catalogue de prestations</h1>
            <p class="text-muted mb-0">Bibliothèque réutilisable dans vos devis (module Bâtiment).</p>
        </div>
        <a href="{{ route('catalog.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle prestation</a>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('catalog.index') }}" class="btn btn-sm {{ !$trade ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        @foreach(\App\Models\CatalogItem::TRADES as $key => $label)
            <a href="{{ route('catalog.index', ['trade' => $key]) }}" class="btn btn-sm {{ $trade === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Métier</th><th>Prestation</th><th>Nature</th><th>Unité</th><th class="text-end">P.U. HT</th><th class="text-end">TVA</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><span class="badge text-bg-light text-dark">{{ $item->tradeLabel() }}</span></td>
                            <td class="fw-semibold">{{ $item->label }}</td>
                            <td>{{ $item->lineTypeLabel() }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-end">@eur($item->unit_price)</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($item->tax_rate,2,',',''),'0'),',') }} %</td>
                            <td class="text-end">
                                <a href="{{ route('catalog.edit', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('catalog.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucune prestation. Ajoutez-en pour les réutiliser dans vos devis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $items->links() }}</div>
@endsection
