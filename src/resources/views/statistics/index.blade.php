@extends('layouts.app')
@section('title', 'Statistiques')

@section('content')
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Statistiques</h1>

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        @php($cards = [
            ['CA encaissé', $kpis['revenue'], '#059669', 'bi-cash-stack'],
            ['Facturé (année)', $kpis['invoiced'], '#2563eb', 'bi-receipt'],
            ['Impayés', $kpis['unpaid'], '#dc2626', 'bi-exclamation-circle'],
        ])
        @foreach($cards as [$label, $val, $color, $icon])
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:{{ $color }}"><i class="bi {{ $icon }} fs-5"></i></div>
                    <div>
                        <div class="text-muted small text-uppercase">{{ $label }}</div>
                        <div class="h5 fw-bold mb-0">@eur($val)</div>
                    </div>
                </div></div>
            </div>
        @endforeach
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:#7c3aed"><i class="bi bi-people-fill fs-5"></i></div>
                <div>
                    <div class="text-muted small text-uppercase">Clients</div>
                    <div class="h5 fw-bold mb-0">{{ $kpis['clients'] }}</div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Chiffre d'affaires encaissé (12 mois)</h2>
                <canvas id="revenueChart" height="120"></canvas>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Factures par statut</h2>
                <canvas id="invoiceChart"></canvas>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Devis par statut</h2>
                <canvas id="quoteChart" height="160"></canvas>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Top clients (facturé)</h2>
                @forelse($topClients as $c)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <a href="{{ route('clients.show', $c) }}" class="text-decoration-none">{{ $c->name }}</a>
                        <strong>@eur($c->invoices_sum_total_ttc)</strong>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Aucune donnée de facturation.</p>
                @endforelse
            </div></div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const eur = v => v.toLocaleString('fr-FR') + ' €';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json($revenueLabels),
            datasets: [{ label: 'CA encaissé', data: @json($revenueData), backgroundColor: '#2563eb', borderRadius: 4 }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: eur } } } },
    });

    new Chart(document.getElementById('invoiceChart'), {
        type: 'doughnut',
        data: {
            labels: @json(collect($invoiceStatus)->keys()->map(fn($s) => \App\Models\Invoice::STATUSES[$s] ?? $s)->values()),
            datasets: [{ data: @json(collect($invoiceStatus)->values()), backgroundColor: ['#dc2626', '#0ea5e9', '#059669'] }],
        },
    });

    new Chart(document.getElementById('quoteChart'), {
        type: 'bar',
        data: {
            labels: @json(collect($quoteStatus)->keys()->map(fn($s) => \App\Models\Quote::STATUSES[$s] ?? $s)->values()),
            datasets: [{ label: 'Devis', data: @json(collect($quoteStatus)->values()), backgroundColor: '#7c3aed', borderRadius: 4 }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { precision: 0 } } } },
    });
</script>
@endpush
