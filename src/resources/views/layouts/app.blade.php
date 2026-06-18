<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Tableau de bord')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --af-primary: #2563eb; }
        body { background: #f1f5f9; }
        .navbar-brand .brand-mark {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            display: inline-flex; align-items: center; justify-content: center; color: #fff;
        }
        .af-shell { max-width: 1200px; }
        /* Menu utilisateur : défile si plus haut que l'écran */
        .app-menu {
            min-width: 250px;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        /* Sur mobile (navbar repliée), le menu prend la largeur dispo et défile avec la barre */
        @media (max-width: 991.98px) {
            .app-menu {
                max-height: calc(100vh - 140px);
                width: 100%;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container af-shell">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}">
                <span class="brand-mark"><i class="bi bi-wrench-adjustable-circle-fill"></i></span>
                {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                @php($u = auth()->user())
                <div class="dropdown ms-auto">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark"
                       data-bs-toggle="dropdown">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center me-2"
                              style="width:36px;height:36px;">{{ strtoupper(substr($u->name,0,1)) }}</span>
                        <span class="d-none d-sm-block text-start lh-sm">
                            <strong class="d-block small">{{ $u->name }}</strong>
                            <span class="text-muted" style="font-size:.75rem;">{{ $u->roleLabel() }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow app-menu">
                        <li><span class="dropdown-item-text small text-muted">{{ $u->company?->name }}</span></li>
                        <li><hr class="dropdown-divider"></li>

                        {{-- Applications --}}
                        <li><a class="dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2 me-2"></i>Accueil</a></li>
                        @if($u->canAccessModule('clients'))
                            <li><a class="dropdown-item {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}"><i class="bi bi-people me-2"></i>Clients</a></li>
                        @endif
                        @if($u->canAccessModule('quotes'))
                            <li><a class="dropdown-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}" href="{{ route('quotes.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Devis</a></li>
                        @endif
                        @if($u->canAccessModule('invoices'))
                            <li><a class="dropdown-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}"><i class="bi bi-receipt me-2"></i>Factures</a></li>
                        @endif
                        @if($u->canAccessModule('stock'))
                            <li><a class="dropdown-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="bi bi-box-seam me-2"></i>Stock</a></li>
                        @endif
                        @if($u->canAccessModule('expenses'))
                            <li><a class="dropdown-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><i class="bi bi-cash-coin me-2"></i>Dépenses</a></li>
                        @endif
                        @if($u->canAccessModule('planning'))
                            <li><a class="dropdown-item {{ request()->routeIs('interventions.*') ? 'active' : '' }}" href="{{ route('interventions.index') }}"><i class="bi bi-calendar-week me-2"></i>Planning</a></li>
                        @endif
                        @if($u->canAccessModule('projects'))
                            <li><a class="dropdown-item {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}"><i class="bi bi-bricks me-2"></i>Chantiers</a></li>
                        @endif
                        <li><a class="dropdown-item {{ request()->routeIs('leaves.*') ? 'active' : '' }}" href="{{ route('leaves.index') }}"><i class="bi bi-umbrella me-2"></i>Congés</a></li>

                        {{-- Modules métiers (verticaux) activés pour l'entreprise --}}
                        @foreach(config('sector_modules') as $skey => $smod)
                            @if(($smod['available'] ?? false) && ($smod['route'] ?? null) && $u->company?->hasModule($skey))
                                <li><a class="dropdown-item" href="{{ route($smod['route']) }}"><i class="bi {{ $smod['icon'] }} me-2"></i>{{ $smod['label'] }}</a></li>
                            @endif
                        @endforeach
                        @if($u->canAccessModule('documents'))
                            <li><a class="dropdown-item {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}"><i class="bi bi-folder me-2"></i>Documents</a></li>
                        @endif

                        {{-- Finance & pilotage --}}
                        @if($u->canAccessModule('statistics') || $u->canAccessModule('accounting') || $u->canAccessModule('assistant'))
                            <li><hr class="dropdown-divider"></li>
                            @if($u->canAccessModule('statistics'))
                                <li><a class="dropdown-item {{ request()->routeIs('statistics.*') ? 'active' : '' }}" href="{{ route('statistics.index') }}"><i class="bi bi-graph-up-arrow me-2"></i>Statistiques</a></li>
                            @endif
                            @if($u->canAccessModule('accounting'))
                                <li><a class="dropdown-item {{ request()->routeIs('accounting.*') ? 'active' : '' }}" href="{{ route('accounting.index') }}"><i class="bi bi-calculator me-2"></i>Comptabilité</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('bank.*') ? 'active' : '' }}" href="{{ route('bank.index') }}"><i class="bi bi-bank me-2"></i>Banque</a></li>
                            @endif
                            @if($u->canAccessModule('assistant'))
                                <li><a class="dropdown-item {{ request()->routeIs('assistant.*') ? 'active' : '' }}" href="{{ route('assistant.index') }}"><i class="bi bi-robot me-2"></i>Assistant IA</a></li>
                            @endif
                        @endif

                        {{-- Administration --}}
                        @if($u->isAdmin())
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}"><i class="bi bi-person-badge me-2"></i>Employés</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock me-2"></i>Rôles &amp; accès</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('modules.*') ? 'active' : '' }}" href="{{ route('modules.catalog') }}"><i class="bi bi-puzzle me-2"></i>Catalogue de modules</a></li>
                        @endif

                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container af-shell py-4">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Visualiseur de fichiers (PDF / images) réutilisable dans toute l'app --}}
    <div class="modal fade" id="fileViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="height:88vh;">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate" id="fileViewerTitle">Aperçu</h5>
                    <a id="fileViewerDownload" class="btn btn-sm btn-outline-secondary ms-auto me-2" href="#" download>
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0 bg-body-secondary">
                    <iframe id="fileViewerFrame" title="Aperçu du document"
                            style="width:100%;height:100%;border:0;display:none;"></iframe>
                    <div id="fileViewerFallback" class="d-none h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4 text-center">
                        <i class="bi bi-file-earmark-arrow-down fs-1 mb-2"></i>
                        <p class="mb-2">Ce type de fichier ne peut pas être prévisualisé dans le navigateur.</p>
                        <a id="fileViewerFallbackDl" class="btn btn-primary" href="#" download><i class="bi bi-download me-1"></i>Télécharger le fichier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Ouvre le visualiseur au clic sur tout élément [data-viewer-url].
    // Attributs : data-viewer-url (aperçu inline), data-viewer-download, data-viewer-name, data-viewer-previewable="1|0"
    (function () {
        const modalEl = document.getElementById('fileViewerModal');
        const modal = new bootstrap.Modal(modalEl);
        const frame = document.getElementById('fileViewerFrame');
        const fallback = document.getElementById('fileViewerFallback');
        const title = document.getElementById('fileViewerTitle');
        const dl = document.getElementById('fileViewerDownload');
        const dlFb = document.getElementById('fileViewerFallbackDl');

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-viewer-url]');
            if (!trigger) return;
            e.preventDefault();

            const url = trigger.dataset.viewerUrl;
            const name = trigger.dataset.viewerName || 'Document';
            const downloadUrl = trigger.dataset.viewerDownload || url;
            const previewable = trigger.dataset.viewerPreviewable !== '0';

            title.textContent = name;
            dl.href = downloadUrl;
            dlFb.href = downloadUrl;

            if (previewable) {
                frame.src = url;
                frame.style.display = 'block';
                fallback.classList.add('d-none');
            } else {
                frame.style.display = 'none';
                frame.src = 'about:blank';
                fallback.classList.remove('d-none');
            }
            modal.show();
        });

        // Libère l'iframe à la fermeture (stoppe le rendu PDF).
        modalEl.addEventListener('hidden.bs.modal', () => { frame.src = 'about:blank'; });
    })();
    </script>
    @stack('scripts')
</body>
</html>
