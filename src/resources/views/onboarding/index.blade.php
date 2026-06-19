@extends('layouts.app')
@section('title', 'Choix des modules')

@section('content')
    <div class="text-center mb-4">
        <h1 class="h3 fw-bold mb-1"><i class="bi bi-rocket-takeoff text-primary me-2"></i>Bienvenue sur ArtisanFlow</h1>
        <p class="text-muted mb-0">Choisissez les modules à activer pour votre entreprise. Vous pourrez les modifier à tout moment dans le catalogue.</p>
    </div>

    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        @foreach($modules as $group => $items)
            <h2 class="h6 text-uppercase text-muted mt-4 mb-2">{{ $group === 'Socle' ? 'Modules de base' : 'Modules métiers (optionnels)' }}</h2>
            <div class="row g-3">
                @foreach($items as $m)
                    <div class="col-md-6 col-lg-4">
                        <label class="card border-0 shadow-sm h-100 w-100" style="cursor:pointer;">
                            <div class="card-body d-flex align-items-start gap-2">
                                <input class="form-check-input mt-1" type="checkbox" name="modules[]" value="{{ $m['key'] }}" @checked($m['checked'])>
                                <span class="rounded d-inline-flex align-items-center justify-content-center text-white flex-shrink-0" style="width:40px;height:40px;background:{{ $m['color'] }}"><i class="bi {{ $m['icon'] }}"></i></span>
                                <span>
                                    <span class="fw-semibold d-block">{{ $m['label'] }}</span>
                                    <span class="text-muted small">{{ $m['description'] }}</span>
                                </span>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="d-flex justify-content-center gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Démarrer avec ces modules</button>
        </div>
    </form>
@endsection
