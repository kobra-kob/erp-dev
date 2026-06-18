@extends('layouts.guest')
@section('title', 'Créer mon entreprise')

@section('content')
    <h2 class="h4 fw-bold mb-1">Créer mon entreprise</h2>
    <p class="text-muted mb-4">Vous serez l'administrateur de votre espace.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nom de l'entreprise</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}" required autofocus
                   class="form-control @error('company_name') is-invalid @enderror" placeholder="Plomberie Dupont">
            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Votre nom</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="form-control @error('name') is-invalid @enderror" placeholder="Louis Dupont">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="form-control @error('email') is-invalid @enderror" placeholder="vous@exemple.fr">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" required
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmation</label>
                <input type="password" name="password_confirmation" required class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-rocket-takeoff me-1"></i>Démarrer
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0 small">
        Déjà un compte ? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Se connecter</a>
    </p>
@endsection
