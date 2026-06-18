<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($i = $invoice)
    <p>Bonjour {{ $i->client?->name }},</p>

    <p>Veuillez trouver ci-joint notre facture <strong>{{ $i->number }}</strong>
        d'un montant de <strong>{{ number_format($i->total_ttc, 2, ',', ' ') }} € TTC</strong>
        @if($i->due_date), à régler avant le {{ $i->due_date->format('d/m/Y') }}@endif.</p>

    <p>Nous vous remercions de votre confiance.</p>

    <p>Cordialement,<br>{{ $i->company?->name }}</p>
</body>
</html>
