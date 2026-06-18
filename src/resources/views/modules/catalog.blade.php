@extends('layouts.app')
@section('title', 'Catalogue de modules')

@section('content')
    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-puzzle-fill text-primary me-2"></i>Catalogue de modules</h1>
        <p class="text-muted mb-0">Activez ou désactivez des modules métiers pour votre entreprise. La désactivation conserve vos données.</p>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-3">
        @foreach($modules as $m)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 {{ $m['active'] ? 'border-success' : '' }}" style="{{ $m['active'] ? 'box-shadow:0 0 0 2px #19875420 !important;' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="rounded d-inline-flex align-items-center justify-content-center text-white" style="width:44px;height:44px;background:{{ $m['color'] }}"><i class="bi {{ $m['icon'] }} fs-5"></i></span>
                            <div>
                                <div class="fw-bold">{{ $m['label'] }}
                                    @if($m['active'])<span class="badge text-bg-success ms-1">Activé</span>@endif
                                </div>
                                <div class="text-muted small">{{ $m['sector'] }}</div>
                            </div>
                        </div>
                        <p class="text-muted small flex-grow-1">{{ $m['description'] }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ $m['price'] }}</span>
                            @if(! $m['available'])
                                <button class="btn btn-sm btn-outline-secondary" disabled>Prochainement</button>
                            @else
                                <form method="POST" action="{{ route('modules.toggle', $m['key']) }}">
                                    @csrf
                                    @if($m['active'])
                                        <button class="btn btn-sm btn-outline-danger">Désactiver</button>
                                    @else
                                        <button class="btn btn-sm btn-success">Activer</button>
                                    @endif
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
