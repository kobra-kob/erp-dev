@extends('layouts.guest')
@section('title', 'Mot de passe oublié')

@section('content')
    <h2 class="h4 fw-bold mb-1">Mot de passe oublié</h2>
    <p class="text-muted mb-4">Saisissez votre e-mail, nous vous enverrons un lien de réinitialisation.</p>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif
    @error('email')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label">Adresse e-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-control" placeholder="vous@exemple.fr">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-send me-1"></i>Envoyer le lien
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0 small">
        <a href="{{ route('login') }}" class="text-decoration-none">&larr; Retour à la connexion</a>
    </p>
@endsection
