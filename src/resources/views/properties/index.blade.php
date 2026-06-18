@extends('layouts.app')
@section('title', 'Biens immobiliers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-house-door-fill me-2" style="color:#7c3aed"></i>Biens</h1>
            <p class="text-muted mb-0">{{ $properties->total() }} bien(s).</p>
        </div>
        <a href="{{ route('properties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau bien</a>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('properties.index') }}" class="btn btn-sm {{ !$status && !$transaction ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        <a href="{{ route('properties.index', ['transaction' => 'vente']) }}" class="btn btn-sm {{ $transaction === 'vente' ? 'btn-dark' : 'btn-outline-secondary' }}">Vente</a>
        <a href="{{ route('properties.index', ['transaction' => 'location']) }}" class="btn btn-sm {{ $transaction === 'location' ? 'btn-dark' : 'btn-outline-secondary' }}">Location</a>
        @foreach(\App\Models\Property::STATUSES as $key => $label)
            <a href="{{ route('properties.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="row g-3">
        @forelse($properties as $p)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h2 class="h6 fw-bold mb-0">{{ $p->title }}</h2>
                            <span class="badge text-bg-{{ $p->statusColor() }}">{{ $p->statusLabel() }}</span>
                        </div>
                        <div class="text-muted small mb-2">
                            {{ $p->typeLabel() }} · {{ $p->transaction === 'vente' ? 'Vente' : 'Location' }}
                            @if($p->surface)· {{ rtrim(rtrim(number_format($p->surface,2,',',' '),'0'),',') }} m²@endif
                            @if($p->rooms)· {{ $p->rooms }} p.@endif
                            @if($p->dpe)· DPE {{ $p->dpe }}@endif
                        </div>
                        <div class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ trim($p->zip.' '.$p->city) ?: '—' }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold mb-0">@eur($p->price){{ $p->transaction === 'location' ? '/mois' : '' }}</span>
                            <a href="{{ route('properties.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><i class="bi bi-house fs-1 d-block mb-2"></i>Aucun bien.</div></div></div>
        @endforelse
    </div>

    <div class="mt-3">{{ $properties->links() }}</div>
@endsection
