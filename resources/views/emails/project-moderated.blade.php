<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Projet modéré</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <h2 style="color: #d97706;">Action administrative sur votre projet</h2>

    <p>Bonjour {{ $user->username ?? $user->email }},</p>

    <p>Votre projet <strong>{{ $project->name }}</strong> a été archivé par l'équipe de modération de GameFabrick.</p>
    <p>Il n'apparaît plus dans la galerie publique. Si vous souhaitez republier ce projet, désarchivez-le depuis votre espace après avoir corrigé les éléments concernés.</p>

    <div style="background: #fef3c7; border-left: 4px solid #d97706; padding: 12px 16px; margin: 16px 0;">
        <p style="margin: 0;"><strong>Motif :</strong> {{ $action->reasonLabel() }}</p>
        @if ($action->reason_text)
            <p style="margin: 8px 0 0;"><strong>Précision :</strong> {{ $action->reason_text }}</p>
        @endif
    </div>

    <p>Pour toute question, vous pouvez nous contacter en réponse à cet email.</p>

    <p style="color: #666; font-size: 0.85em; margin-top: 32px; border-top: 1px solid #eee; padding-top: 16px;">
        L'équipe GameFabrick
    </p>
</body>
</html>