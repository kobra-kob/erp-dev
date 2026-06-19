@extends('layouts.app')
@section('title', $product->name)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Stock</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $product->name }}</h1>
            <p class="text-muted mb-0">
                {{ $product->reference ?: 'Sans référence' }}
                · {{ $product->kindLabel() }}
                @if($product->category) · {{ $product->category }}@endif
                @if($product->is_sellable)
                    <span class="badge text-bg-success ms-1">Vendable</span>
                @else
                    <span class="badge text-bg-secondary ms-1">Non vendable</span>
                @endif
            </p>
        </div>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    @if($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height:280px;object-fit:contain;">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded text-muted" style="height:220px;">
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Prix d'achat</div>
                            <div class="fw-bold">@eur($product->purchase_price)</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Prix de vente HT</div>
                            <div class="fw-bold">@eur($product->sale_price)</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Marge</div>
                            <div class="fw-bold">@eur($product->margin()) @if($product->marginPercent() !== null)<span class="text-muted small">({{ $product->marginPercent() }} %)</span>@endif</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">TVA</div>
                            <div class="fw-bold">{{ rtrim(rtrim(number_format($product->tax_rate, 2, ',', ' '), '0'), ',') }} %</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="text-muted small">Stock actuel</div>
                            <div class="fw-bold fs-5">
                                {{ rtrim(rtrim(number_format($product->stock, 2, ',', ' '), '0'), ',') }} {{ $product->unit }}
                                @if($product->isLowStock())<span class="badge text-bg-danger ms-1">Alerte</span>@endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Stock minimum</div>
                            <div class="fw-bold fs-5">{{ rtrim(rtrim(number_format($product->min_stock, 2, ',', ' '), '0'), ',') }} {{ $product->unit }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($product->description)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Description</h2>
                        <p class="mb-0" style="white-space:pre-line;">{{ $product->description }}</p>
                    </div>
                </div>
            @endif

            @if($product->supplier_name || $product->supplier_email)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Fournisseur</h2>
                        <p class="mb-0">{{ $product->supplier_name ?: '—' }}
                            @if($product->supplier_email)<span class="text-muted">· {{ $product->supplier_email }}</span>@endif
                        </p>
                    </div>
                </div>
            @endif

            @if($product->purchaseOrders->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Dernières commandes fournisseur</h2>
                        <ul class="list-unstyled mb-0 small">
                            @foreach($product->purchaseOrders as $po)
                                <li class="d-flex justify-content-between border-bottom py-1">
                                    <span>{{ optional($po->ordered_at)->format('d/m/Y') }}</span>
                                    <span>{{ rtrim(rtrim(number_format($po->quantity, 2, ',', ' '), '0'), ',') }} {{ $product->unit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
