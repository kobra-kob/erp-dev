<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support ArtisanFlow — Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>body { background: #0b1120; }</style>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
    <div class="container" style="max-width:420px;">
        <div class="text-center mb-4 text-white">
            <i class="bi bi-shield-lock-fill display-5 text-danger"></i>
            <h1 class="h4 mt-2">Console de support</h1>
            <p class="text-white-50 small mb-0">Accès réservé — administration multi-tenant</p>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('support.login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label small">Se souvenir de moi</label>
                    </div>
                    <button class="btn btn-danger w-100"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
