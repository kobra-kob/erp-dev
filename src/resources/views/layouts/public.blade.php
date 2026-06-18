<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Devis')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>body{background:#f1f5f9;} .af-card{max-width:880px;}</style>
</head>
<body>
    <nav class="navbar bg-white border-bottom mb-4">
        <div class="container af-card">
            <span class="navbar-brand d-flex align-items-center gap-2 fw-bold">
                <span style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#2563eb,#06b6d4);display:inline-flex;align-items:center;justify-content:center;color:#fff;"><i class="bi bi-wrench-adjustable-circle-fill"></i></span>
                {{ config('app.name') }}
            </span>
        </div>
    </nav>

    <main class="container af-card pb-5">
        @if(session('status'))
            <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @yield('content')
        <p class="text-center text-muted small mt-4">Document transmis via {{ config('app.name') }}.</p>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
