@extends('layouts.guest')
@section('title', 'Connexion')

@section('content')
    <h2 class="h4 fw-bold mb-1">Connexion</h2>
    <p class="text-muted mb-4">Accédez à votre espace de gestion.</p>

    @if ($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-control @error('email') is-invalid @enderror" placeholder="vous@exemple.fr">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" required class="form-control" placeholder="••••••••">
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0 small">
        Pas encore de compte ? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Créer mon entreprise</a>
    </p>
@endsection
