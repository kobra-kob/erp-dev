@extends('layouts.guest')
@section('title', 'Vérification en deux étapes')

@section('content')
    <div class="text-center mb-3">
        <i class="bi bi-shield-lock-fill text-primary" style="font-size:2.5rem;"></i>
    </div>
    <h2 class="h4 fw-bold mb-1 text-center">Vérification en deux étapes</h2>
    <p class="text-muted mb-4 text-center">Saisissez le code à 6 chiffres de votre application d'authentification.</p>

    @error('code')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

    <form method="POST" action="{{ route('two-factor.challenge') }}">
        @csrf
        <div class="mb-4">
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                   maxlength="7" required autofocus
                   class="form-control form-control-lg text-center fs-3 letter-spacing"
                   placeholder="000000" style="letter-spacing:.5rem;">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-unlock me-1"></i>Vérifier
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0 small">
        <a href="{{ route('login') }}" class="text-decoration-none">&larr; Annuler</a>
    </p>
@endsection
