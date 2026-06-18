<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1f2937; margin: 0; }
        .header { width: 100%; margin-bottom: 30px; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: #059669; }
        .muted { color: #6b7280; }
        .doc-title { font-size: 26px; font-weight: bold; text-align: right; }
        .doc-meta { text-align: right; }
        .box { background: #f3f4f6; padding: 10px 12px; border-radius: 6px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th { background: #1f2937; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .right { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 15px; border-collapse: collapse; }
        .totals td { padding: 5px 8px; }
        .totals .grand { font-size: 15px; font-weight: bold; border-top: 2px solid #1f2937; }
        .totals .due { font-weight: bold; color: #b91c1c; }
        .notes { margin-top: 25px; font-size: 11px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #9ca3af; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .b-paid { background: #d1fae5; color: #065f46; }
        .b-unpaid { background: #fee2e2; color: #991b1b; }
        .b-partial { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    @php($company = $invoice->company)
    @php($cls = ['paid'=>'b-paid','partial'=>'b-partial','unpaid'=>'b-unpaid'][$invoice->status] ?? 'b-unpaid')
    <table class="header">
        <tr>
            <td style="width:55%;">
                <div class="brand">{{ $company->name }}</div>
                <div class="muted">
                    {{ $company->address }}<br>
                    {{ trim($company->zip . ' ' . $company->city) }}<br>
                    @if($company->phone){{ $company->phone }}<br>@endif
                    @if($company->email){{ $company->email }}<br>@endif
                    @if($company->siret)SIRET : {{ $company->siret }}@endif
                </div>
            </td>
            <td style="width:45%;">
                <div class="doc-title">FACTURE</div>
                <div class="doc-meta muted">
                    N° <strong>{{ $invoice->number }}</strong><br>
                    Date : {{ $invoice->issue_date->format('d/m/Y') }}<br>
                    @if($invoice->due_date)Échéance : {{ $invoice->due_date->format('d/m/Y') }}<br>@endif
                    <span class="badge {{ $cls }}">{{ $invoice->statusLabel() }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-bottom:10px;">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%;">
                <div class="box">
                    <strong>Client</strong><br>
                    {{ $invoice->client->name }}<br>
                    @if($invoice->client->contact_name){{ $invoice->client->contact_name }}<br>@endif
                    {{ $invoice->client->address }}<br>
                    {{ trim($invoice->client->zip . ' ' . $invoice->client->city) }}
                </div>
            </td>
        </tr>
    </table>

    @if($invoice->title)<p><strong>Objet :</strong> {{ $invoice->title }}</p>@endif

    <table class="items">
        <thead>
            <tr>
                <th>Nature</th><th>Description</th>
                <th class="right">Qté</th><th class="right">P.U. HT</th>
                <th class="right">TVA</th><th class="right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->lines as $line)
                <tr>
                    <td>{{ $line->typeLabel() }}</td>
                    <td>{{ $line->description }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format($line->quantity, 2, ',', ' '), '0'), ',') }}</td>
                    <td class="right">@eur($line->unit_price)</td>
                    <td class="right">{{ rtrim(rtrim(number_format($line->tax_rate, 2, ',', ''), '0'), ',') }} %</td>
                    <td class="right">@eur($line->lineHt())</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Total HT</td><td class="right">@eur($invoice->subtotal_ht)</td></tr>
        @foreach($invoice->taxBreakdown() as $rate => $b)
            <tr><td class="muted">TVA {{ rtrim(rtrim($rate, '0'), '.') }} %</td><td class="right">@eur($b['tax'])</td></tr>
        @endforeach
        <tr class="grand"><td>Total TTC</td><td class="right">@eur($invoice->total_ttc)</td></tr>
        @if((float)$invoice->paid_amount > 0)
            <tr><td>Déjà réglé</td><td class="right">@eur($invoice->paid_amount)</td></tr>
            <tr class="due"><td>Restant dû</td><td class="right">@eur($invoice->remainingAmount())</td></tr>
        @endif
    </table>

    @if($invoice->notes)<div class="notes"><strong>Notes :</strong><br>{{ $invoice->notes }}</div>@endif

    <div class="footer">
        Facture générée par ArtisanFlow ERP — {{ $company->name }}
    </div>
</body>
</html>
