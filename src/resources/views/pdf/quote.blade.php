@php($company = $quote->company)
@php($logo = $company->logoDataUri())
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1f2937; margin: 0; }
        .header { width: 100%; margin-bottom: 30px; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: {{ $company->brandColor() }}; }
        .logo { max-height: 70px; max-width: 200px; margin-bottom: 6px; }
        .muted { color: #6b7280; }
        .doc-title { font-size: 26px; font-weight: bold; text-align: right; color: {{ $company->brandColor() }}; }
        .doc-meta { text-align: right; }
        .box { background: #f3f4f6; padding: 10px 12px; border-radius: {{ $company->documentRadius() }}; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.items th { background: {{ $company->brandAccent() }}; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .right { text-align: right; }
        .totals { width: 45%; margin-left: 55%; margin-top: 15px; border-collapse: collapse; }
        .totals td { padding: 5px 8px; }
        .totals .grand { font-size: 15px; font-weight: bold; border-top: 2px solid {{ $company->brandAccent() }}; }
        .notes { margin-top: 25px; font-size: 11px; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #9ca3af; }
        .badge { padding: 3px 8px; border-radius: 4px; background: #fef3c7; color: #92400e; font-size: 11px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:55%;">
                @if($logo)<img src="{{ $logo }}" class="logo" alt="">@endif
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
                <div class="doc-title">DEVIS</div>
                <div class="doc-meta muted">
                    N° <strong>{{ $quote->number }}</strong><br>
                    Date : {{ $quote->issue_date->format('d/m/Y') }}<br>
                    @if($quote->valid_until)Valable jusqu'au : {{ $quote->valid_until->format('d/m/Y') }}<br>@endif
                    <span class="badge">{{ $quote->statusLabel() }}</span>
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
                    {{ $quote->client->name }}<br>
                    @if($quote->client->contact_name){{ $quote->client->contact_name }}<br>@endif
                    {{ $quote->client->address }}<br>
                    {{ trim($quote->client->zip . ' ' . $quote->client->city) }}
                </div>
            </td>
        </tr>
    </table>

    @if($quote->title)<p><strong>Objet :</strong> {{ $quote->title }}</p>@endif

    <table class="items">
        <thead>
            <tr>
                <th>Nature</th><th>Description</th>
                <th class="right">Qté</th><th class="right">P.U. HT</th>
                <th class="right">TVA</th><th class="right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->lines as $line)
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
        <tr><td>Total HT</td><td class="right">@eur($quote->subtotal_ht)</td></tr>
        @foreach($quote->taxBreakdown() as $rate => $b)
            <tr><td class="muted">TVA {{ rtrim(rtrim($rate, '0'), '.') }} %</td><td class="right">@eur($b['tax'])</td></tr>
        @endforeach
        <tr class="grand"><td>Total TTC</td><td class="right">@eur($quote->total_ttc)</td></tr>
    </table>

    @if($quote->notes)<div class="notes"><strong>Notes :</strong><br>{{ $quote->notes }}</div>@endif

    <div class="footer">
        Devis généré par ArtisanFlow ERP — {{ $company->name }}
    </div>
</body>
</html>
