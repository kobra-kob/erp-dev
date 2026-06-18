@extends('layouts.guest')
@section('title', 'Nouveau mot de passe')

@section('content')
    <h2 class="h4 fw-bold mb-1">Nouveau mot de passe</h2>
    <p class="text-muted mb-4">Choisissez un nouveau mot de passe sécurisé.</p>

    @error('email')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required
                   class="form-control" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="password" required autofocus
                   class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Confirmation</label>
            <input type="password" name="password_confirmation" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-check2-circle me-1"></i>Réinitialiser
        </button>
    </form>
@endsection
