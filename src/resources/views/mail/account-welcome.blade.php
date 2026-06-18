<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937;">
    <p>Bonjour {{ $user->name }},</p>

    @if($createdByAdmin)
        <p>Un compte vient d'être créé pour vous sur <strong>{{ config('app.name') }}</strong>
        ({{ $user->company?->name }}).</p>
        <p>Votre identifiant de connexion : <strong>{{ $user->email }}</strong>.<br>
        Pour définir votre mot de passe, utilisez le lien « Mot de passe oublié » sur la page de connexion.</p>
    @else
        <p>Bienvenue sur <strong>{{ config('app.name') }}</strong> ! Votre entreprise
        « {{ $user->company?->name }} » a bien été créée et votre compte administrateur est actif.</p>
        <p>Votre identifiant de connexion : <strong>{{ $user->email }}</strong>.</p>
    @endif

    <p style="margin-top:16px;">
        <a href="{{ route('login') }}" style="background:#2563eb;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">
            Se connecter
        </a>
    </p>

    <p style="color:#6b7280;font-size:13px;margin-top:20px;">
        ERP de gestion pour artisans — Clients, devis, factures, planning, comptabilité…
    </p>
</body>
</html>
