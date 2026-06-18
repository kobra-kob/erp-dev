<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export comptable au format CSV (séparateur « ; » + BOM UTF-8 pour Excel FR).
 */
class ExportController extends Controller
{
    /** Journal des factures de l'année. */
    public function invoices(Request $request): StreamedResponse
    {
        $year = (int) $request->query('year', now()->year);

        $invoices = Invoice::with('client')
            ->whereYear('issue_date', $year)
            ->orderBy('issue_date')->orderBy('number')
            ->get();

        $rows = $invoices->map(fn (Invoice $i) => [
            $i->number,
            $i->issue_date->format('d/m/Y'),
            optional($i->due_date)->format('d/m/Y'),
            $i->client?->name,
            $this->num($i->subtotal_ht),
            $this->num($i->tax_amount),
            $this->num($i->total_ttc),
            $this->num($i->paid_amount),
            $this->num($i->remainingAmount()),
            $i->statusLabel(),
        ]);

        return $this->stream(
            "factures-{$year}.csv",
            ['Numéro', 'Date', 'Échéance', 'Client', 'HT', 'TVA', 'TTC', 'Payé', 'Restant dû', 'Statut'],
            $rows,
        );
    }

    /** Journal des règlements (recettes) de l'année. */
    public function payments(Request $request): StreamedResponse
    {
        $year = (int) $request->query('year', now()->year);

        $payments = Payment::with('invoice.client')
            ->whereYear('paid_at', $year)
            ->orderBy('paid_at')
            ->get();

        $rows = $payments->map(fn (Payment $p) => [
            $p->paid_at->format('d/m/Y'),
            $p->invoice?->number,
            $p->invoice?->client?->name,
            $this->num($p->amount),
            $p->methodLabel(),
            $p->note,
        ]);

        return $this->stream(
            "reglements-{$year}.csv",
            ['Date', 'Facture', 'Client', 'Montant', 'Moyen', 'Note'],
            $rows,
        );
    }

    private function num(float|string|null $v): string
    {
        return number_format((float) $v, 2, ',', '');
    }

    /**
     * @param  array<int, string>  $header
     * @param  \Illuminate\Support\Collection<int, array<int, mixed>>  $rows
     */
    private function stream(string $filename, array $header, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 (accents corrects sous Excel)
            fputcsv($out, $header, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
