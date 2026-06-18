<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($o = $order)
    <p>Bonjour {{ $o->supplier_name ?: '' }},</p>

    <p>Nous souhaitons passer la commande suivante :</p>

    <table cellpadding="6" style="border-collapse:collapse;">
        <tr><td><strong>Produit</strong></td><td>{{ $o->product->name }} @if($o->product->reference)({{ $o->product->reference }})@endif</td></tr>
        <tr><td><strong>Quantité</strong></td><td>{{ rtrim(rtrim(number_format($o->quantity, 2, ',', ' '), '0'), ',') }} {{ $o->product->unit }}</td></tr>
        <tr><td><strong>Prix unitaire indicatif</strong></td><td>{{ number_format($o->unit_price, 2, ',', ' ') }} €</td></tr>
        <tr><td><strong>Total estimé</strong></td><td>{{ number_format($o->total(), 2, ',', ' ') }} €</td></tr>
        <tr><td><strong>Date</strong></td><td>{{ $o->ordered_at->format('d/m/Y') }}</td></tr>
    </table>

    <p>Merci de nous confirmer la disponibilité et le délai de livraison.</p>

    <p>Cordialement,<br>{{ $o->company?->name }}</p>
</body>
</html>
