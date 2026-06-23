<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Support ArtisanFlow — @yield('title', 'Console')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #0b1120; }
        .support-shell { max-width: 1200px; }
        .navbar-support { background: #7f1d1d; }
        .badge-online { background: #16a34a; }
        .badge-offline { background: #475569; }
    </style>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-support sticky-top shadow-sm">
        <div class="container support-shell">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('support.tenants.index') }}">
                <i class="bi bi-shield-lock-fill"></i> Support ArtisanFlow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('support.tenants.*') ? 'active fw-semibold' : '' }}" href="{{ route('support.tenants.index') }}">
                            <i class="bi bi-buildings"></i> Tenants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('support.audit.*') ? 'active fw-semibold' : '' }}" href="{{ route('support.audit.index') }}">
                            <i class="bi bi-journal-text"></i> Audit
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white-50 small">
                        <i class="bi bi-person-badge"></i> {{ auth('support')->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('support.logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Quitter</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container support-shell py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
