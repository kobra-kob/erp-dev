<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Bienvenue')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #06b6d4 100%);
        }
        .auth-card { border: none; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .brand-mark { width: 56px; height: 56px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="text-center mb-4 text-white">
                    <img src="{{ asset('favicon.svg') }}" alt="" class="brand-mark mb-3">
                    <h1 class="h3 fw-bold mb-0">{{ config('app.name') }}</h1>
                    <p class="opacity-75 mb-0">ERP pour artisans &amp; petites entreprises</p>
                </div>

                <div class="card auth-card">
                    <div class="card-body p-4 p-md-5">
                        @yield('content')
                    </div>
                </div>

                <p class="text-center text-white-50 mt-4 small mb-0">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
