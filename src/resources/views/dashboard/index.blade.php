@extends('layouts.app')
@section('title', 'Tableau de bord')

@push('head')
<style>
    .kpi-card { border: none; border-radius: .9rem; box-shadow: 0 2px 10px rgba(15,23,42,.05); }
    .module-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .module-card {
        position: relative; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff;
        padding: 1.25rem; text-decoration: none; color: inherit; display: block;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .module-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(15,23,42,.10); }
    .module-icon {
        width: 52px; height: 52px; border-radius: 13px; display: inline-flex;
        align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; margin-bottom: .75rem;
    }
    .module-card.is-soon { opacity: .6; }
    .module-card .badge-soon { position: absolute; top: .6rem; right: .6rem; }
    .module-tools { position: absolute; top: .5rem; right: .5rem; display: none; gap: .25rem; }
    body.editing .module-tools { display: flex; }
    body.editing .module-card { cursor: grab; }
    body.editing .module-card.is-hidden { display: block !important; opacity: .35; border-style: dashed; }
    .module-card.is-hidden { display: none; }
    .fav-star.on { color: #f59e0b; }
    .sortable-ghost { opacity: .3; }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1">Bonjour {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p class="text-muted mb-0">Voici votre activité aujourd'hui.</p>
        </div>
        <button id="editToggle" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil-square me-1"></i><span class="lbl">Personnaliser</span>
        </button>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="module-icon mb-0" style="background:#2563eb"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase">Clients</div>
                        <div class="h4 fw-bold mb-0">{{ $kpis['clients'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="module-icon mb-0" style="background:#7c3aed"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase">Devis @if($kpis['quotes']['soon'])<span class="badge text-bg-light">bientôt</span>@endif</div>
                        <div class="h4 fw-bold mb-0">{{ $kpis['quotes']['value'] }} <span class="fs-6 fw-normal text-muted">{{ $kpis['quotes']['label'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card kpi-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="module-icon mb-0" style="background:#059669"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase">Factures @if($kpis['invoices']['soon'])<span class="badge text-bg-light">bientôt</span>@endif</div>
                        <div class="h4 fw-bold mb-0">{{ $kpis['invoices']['value'] }} <span class="fs-6 fw-normal text-muted">{{ $kpis['invoices']['label'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Applications --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">Applications</h2>
        <span class="text-muted small editing-hint" style="display:none;">
            <i class="bi bi-arrows-move"></i> Glissez pour réorganiser · <i class="bi bi-star"></i> favori · <i class="bi bi-eye-slash"></i> masquer
        </span>
    </div>

    <div id="moduleGrid" class="module-grid">
        @foreach($modules as $m)
            <div class="module-card {{ $m['available'] ? '' : 'is-soon' }} {{ $m['is_hidden'] ? 'is-hidden' : '' }} {{ $m['is_favorite'] ? 'is-favorite' : '' }}"
                 data-key="{{ $m['key'] }}"
                 @if($m['available'] && $m['route']) data-href="{{ route($m['route']) }}" @endif>

                <div class="module-tools">
                    <button type="button" class="btn btn-sm btn-light border fav-star {{ $m['is_favorite'] ? 'on' : '' }}" title="Favori">
                        <i class="bi {{ $m['is_favorite'] ? 'bi-star-fill' : 'bi-star' }}"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light border hide-btn" title="Masquer / afficher">
                        <i class="bi {{ $m['is_hidden'] ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                    </button>
                </div>

                @if(!$m['available'])<span class="badge text-bg-secondary badge-soon">bientôt</span>@endif
                @if($m['is_favorite'])<i class="bi bi-star-fill text-warning position-absolute" style="top:.7rem;left:.7rem;"></i>@endif

                <div class="module-icon" style="background: {{ $m['color'] }}"><i class="bi {{ $m['icon'] }}"></i></div>
                <div class="fw-semibold">{{ $m['label'] }}</div>
                <div class="text-muted small">{{ $m['description'] }}</div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const grid = document.getElementById('moduleGrid');
    const editToggle = document.getElementById('editToggle');
    const hint = document.querySelector('.editing-hint');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const saveUrl = "{{ route('dashboard.preferences') }}";

    // Navigation au clic (hors mode édition)
    grid.addEventListener('click', function (e) {
        const card = e.target.closest('.module-card');
        if (!card || document.body.classList.contains('editing')) return;
        if (e.target.closest('.module-tools')) return;
        const href = card.dataset.href;
        if (href) window.location = href;
    });

    function collectPrefs() {
        const cards = [...grid.querySelectorAll('.module-card')];
        return {
            order: cards.map(c => c.dataset.key),
            hidden: cards.filter(c => c.classList.contains('is-hidden')).map(c => c.dataset.key),
            favorites: cards.filter(c => c.classList.contains('is-favorite')).map(c => c.dataset.key),
        };
    }

    function savePrefs() {
        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(collectPrefs()),
        });
    }

    // Drag & drop
    new Sortable(grid, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        disabled: true,
        onEnd: savePrefs,
        filter: '.module-tools',
    }).option('disabled', true);
    const sortable = Sortable.get(grid);

    // Favori
    grid.addEventListener('click', function (e) {
        const fav = e.target.closest('.fav-star');
        if (!fav) return;
        e.stopPropagation();
        const card = fav.closest('.module-card');
        card.classList.toggle('is-favorite');
        const on = card.classList.contains('is-favorite');
        fav.classList.toggle('on', on);
        fav.querySelector('i').className = 'bi ' + (on ? 'bi-star-fill' : 'bi-star');
        savePrefs();
    });

    // Masquer / afficher
    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.hide-btn');
        if (!btn) return;
        e.stopPropagation();
        const card = btn.closest('.module-card');
        card.classList.toggle('is-hidden');
        const hidden = card.classList.contains('is-hidden');
        btn.querySelector('i').className = 'bi ' + (hidden ? 'bi-eye' : 'bi-eye-slash');
        savePrefs();
    });

    // Bascule du mode édition
    editToggle.addEventListener('click', function () {
        const editing = document.body.classList.toggle('editing');
        sortable.option('disabled', !editing);
        hint.style.display = editing ? 'inline' : 'none';
        editToggle.classList.toggle('btn-primary', editing);
        editToggle.classList.toggle('btn-outline-secondary', !editing);
        editToggle.querySelector('.lbl').textContent = editing ? 'Terminer' : 'Personnaliser';
    });
})();
</script>
@endpush
