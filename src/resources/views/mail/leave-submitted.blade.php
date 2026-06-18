<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($l = $leave)
    <p>Bonjour,</p>
    <p><strong>{{ $l->user?->name }}</strong> a déposé une demande de congés à valider :</p>
    <table cellpadding="6" style="border-collapse:collapse;">
        <tr><td><strong>Type</strong></td><td>{{ $l->typeLabel() }}</td></tr>
        <tr><td><strong>Période</strong></td><td>{{ $l->start_date->format('d/m/Y') }} → {{ $l->end_date->format('d/m/Y') }} ({{ $l->durationDays() }} j)</td></tr>
        @if($l->reason)<tr><td><strong>Motif</strong></td><td>{{ $l->reason }}</td></tr>@endif
    </table>
    <p style="margin-top:14px;"><a href="{{ route('leaves.index') }}" style="background:#2563eb;color:#fff;padding:9px 16px;border-radius:6px;text-decoration:none;">Traiter la demande</a></p>
</body>
</html>
