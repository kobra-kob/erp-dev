{{-- Affichage en lecture seule des lignes + totaux d'un devis/facture --}}
<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Nature</th><th>Description</th>
                <th class="text-end">Qté</th><th class="text-end">P.U. HT</th>
                <th class="text-end">TVA</th><th class="text-end">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
                <tr>
                    <td><span class="badge text-bg-light text-dark">{{ $line->typeLabel() }}</span></td>
                    <td>{{ $line->description }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                    <td class="text-end">@eur($line->unit_price)</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($line->tax_rate, 2, ',', ''), '0'), ',') }} %</td>
                    <td class="text-end">@eur($line->lineHt())</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row justify-content-end mt-2">
    <div class="col-md-6 col-lg-5">
        <table class="table table-sm mb-0">
            <tr><td class="text-muted">Total HT</td><td class="text-end fw-semibold">@eur($document->subtotal_ht)</td></tr>
            @foreach($document->taxBreakdown() as $rate => $b)
                <tr><td class="text-muted">TVA {{ rtrim(rtrim($rate, '0'), '.') }} %</td><td class="text-end">@eur($b['tax'])</td></tr>
            @endforeach
            <tr class="border-top"><td class="fw-bold">Total TTC</td><td class="text-end fw-bold fs-5">@eur($document->total_ttc)</td></tr>
        </table>
    </div>
</div>
