<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($q = $quote)
    <p>Bonjour,</p>
    <p>Le client <strong>{{ $q->client?->name }}</strong> a
        <strong>{{ $q->status === 'accepted' ? 'accepté' : 'refusé' }}</strong>
        le devis <strong>{{ $q->number }}</strong>
        ({{ number_format($q->total_ttc, 2, ',', ' ') }} € TTC).</p>

    @if($invoiceNumber)
        <p>La facture <strong>{{ $invoiceNumber }}</strong> a été générée automatiquement.</p>
    @endif

    <p style="margin-top:14px;">
        <a href="{{ route('quotes.show', $q) }}" style="background:#2563eb;color:#fff;padding:9px 16px;border-radius:6px;text-decoration:none;">Voir le devis</a>
    </p>
</body>
</html>
