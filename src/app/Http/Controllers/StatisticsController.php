<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        // --- KPIs annuels ---
        $kpis = [
            'revenue'   => (float) Invoice::whereYear('issue_date', now()->year)->sum('paid_amount'),
            'invoiced'  => (float) Invoice::whereYear('issue_date', now()->year)->sum('total_ttc'),
            'unpaid'    => round((float) Invoice::whereIn('status', ['unpaid', 'partial'])
                ->selectRaw('COALESCE(SUM(total_ttc - paid_amount),0) d')->value('d'), 2),
            'clients'   => Client::count(),
        ];

        // --- CA encaissé sur 12 mois ---
        $start = now()->subMonths(11)->startOfMonth();
        $rows = Payment::where('paid_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') ym, SUM(amount) total")
            ->groupBy('ym')->pluck('total', 'ym');

        $revenueLabels = [];
        $revenueData = [];
        for ($m = $start->copy(); $m <= now(); $m->addMonth()) {
            $key = $m->format('Y-m');
            $revenueLabels[] = ucfirst($m->translatedFormat('M y'));
            $revenueData[] = round((float) ($rows[$key] ?? 0), 2);
        }

        // --- Répartitions par statut ---
        $invoiceStatus = Invoice::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $quoteStatus = Quote::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');

        // --- Top clients (facturé) ---
        $topClients = Client::withSum('invoices', 'total_ttc')
            ->orderByDesc('invoices_sum_total_ttc')
            ->limit(5)->get()
            ->filter(fn ($c) => (float) $c->invoices_sum_total_ttc > 0)
            ->values();

        return view('statistics.index', [
            'kpis'          => $kpis,
            'revenueLabels' => $revenueLabels,
            'revenueData'   => $revenueData,
            'invoiceStatus' => $invoiceStatus,
            'quoteStatus'   => $quoteStatus,
            'topClients'    => $topClients,
        ]);
    }
}
