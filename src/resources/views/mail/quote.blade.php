<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($q = $quote)
    <p>Bonjour {{ $q->client?->name }},</p>

    <p>Veuillez trouver ci-joint notre devis <strong>{{ $q->number }}</strong>
        @if($q->title) concernant « {{ $q->title }} »@endif, d'un montant de
        <strong>{{ number_format($q->total_ttc, 2, ',', ' ') }} € TTC</strong>
        @if($q->valid_until), valable jusqu'au {{ $q->valid_until->format('d/m/Y') }}@endif.</p>

    <p>Vous pouvez <strong>accepter ou refuser ce devis en ligne</strong> en un clic :</p>
    <p style="margin:16px 0;">
        <a href="{{ $q->publicUrl() }}" style="background:#2563eb;color:#fff;padding:11px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Accepter ou refuser le devis
        </a>
    </p>

    <p>Nous restons à votre disposition pour toute question.</p>

    <p>Cordialement,<br>{{ $q->company?->name }}</p>
</body>
</html>
