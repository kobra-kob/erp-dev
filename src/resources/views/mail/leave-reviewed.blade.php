<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    @php($l = $leave)
    <p>Bonjour {{ $l->user?->name }},</p>
    <p>Votre demande de congés ({{ $l->typeLabel() }}, du {{ $l->start_date->format('d/m/Y') }}
        au {{ $l->end_date->format('d/m/Y') }}) a été
        <strong>{{ mb_strtolower($l->statusLabel()) }}</strong>
        @if($l->reviewer) par {{ $l->reviewer->name }}@endif.</p>
    @if($l->review_comment)<p>Commentaire : « {{ $l->review_comment }} »</p>@endif
    <p style="margin-top:14px;"><a href="{{ route('leaves.index') }}" style="background:#2563eb;color:#fff;padding:9px 16px;border-radius:6px;text-decoration:none;">Voir mes demandes</a></p>
</body>
</html>
