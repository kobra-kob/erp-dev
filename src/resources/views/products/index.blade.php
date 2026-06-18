@extends('layouts.app')
@section('title', 'Stock')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-box-seam-fill text-info me-2"></i>Stock</h1>
            <p class="text-muted mb-0">{{ $products->total() }} produit(s).</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau produit</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    @if($lowCount > 0)
        <div class="alert alert-warning d-flex align-items-center flex-wrap gap-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <span>{{ $lowCount }} produit(s) en stock faible.</span>
            <a href="{{ route('products.index', ['low' => 1]) }}" class="ms-auto btn btn-sm btn-outline-dark">Voir</a>
            <form method="POST" action="{{ route('products.replenish-all') }}">
                @csrf
                <button class="btn btn-sm btn-warning"><i class="bi bi-truck me-1"></i>Tout réapprovisionner</button>
            </form>
        </div>
    @endif

    <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width:360px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Nom ou référence…">
        </div>
        @if($lowOnly)<a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Tout afficher</a>@endif
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th><th>Réf.</th>
                        <th class="text-end">Achat</th><th class="text-end">Vente</th><th class="text-end">Marge</th>
                        <th class="text-center">Stock</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td class="text-muted">{{ $product->reference ?: '—' }}</td>
                            <td class="text-end">@eur($product->purchase_price)</td>
                            <td class="text-end">@eur($product->sale_price)</td>
                            <td class="text-end">{{ $product->marginPercent() !== null ? $product->marginPercent().' %' : '—' }}</td>
                            <td class="text-center">
                                <span class="fw-bold">{{ rtrim(rtrim(number_format($product->stock, 2, ',', ' '), '0'), ',') }}</span>
                                <span class="text-muted small">{{ $product->unit }}</span>
                                @if($product->isLowStock())<span class="badge text-bg-danger ms-1">Alerte</span>@endif
                                <div class="text-muted small">min : {{ rtrim(rtrim(number_format($product->min_stock, 2, ',', ' '), '0'), ',') }}</div>
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                @if($product->canReorder())
                                    <form method="POST" action="{{ route('products.replenish', $product) }}" class="d-inline"
                                          title="Commander {{ rtrim(rtrim(number_format($product->orderQuantity(),2,',',' '),'0'),',') }} {{ $product->unit }} chez {{ $product->supplier_name }}">
                                        @csrf
                                        <button class="btn btn-sm btn-warning"><i class="bi bi-truck"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('products.adjust', $product) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="delta" value="-1">
                                    <button class="btn btn-sm btn-outline-secondary" title="-1"><i class="bi bi-dash"></i></button>
                                </form>
                                <form method="POST" action="{{ route('products.adjust', $product) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="delta" value="1">
                                    <button class="btn btn-sm btn-outline-secondary" title="+1"><i class="bi bi-plus"></i></button>
                                </form>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Aucun produit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
@endsection
