<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($i = $invoice)
    <p>Bonjour {{ $i->client?->name }},</p>

    <p>Sauf erreur de notre part, la facture suivante reste impayée à ce jour :</p>

    <table cellpadding="6" style="border-collapse:collapse;">
        <tr><td><strong>Facture</strong></td><td>{{ $i->number }}</td></tr>
        <tr><td><strong>Date</strong></td><td>{{ $i->issue_date->format('d/m/Y') }}</td></tr>
        <tr><td><strong>Échéance</strong></td><td>{{ optional($i->due_date)->format('d/m/Y') }}</td></tr>
        <tr><td><strong>Montant TTC</strong></td><td>{{ number_format($i->total_ttc, 2, ',', ' ') }} €</td></tr>
        <tr><td><strong>Restant dû</strong></td><td>{{ number_format($i->remainingAmount(), 2, ',', ' ') }} €</td></tr>
    </table>

    <p>Nous vous remercions de bien vouloir procéder à son règlement dans les meilleurs délais.</p>

    <p>Cordialement,<br>{{ $i->company?->name }}</p>
</body>
</html>
