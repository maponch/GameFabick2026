<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;">

  <h2 style="color: #1976D2;">GameFabrick 🎲</h2>

  <p>Bonjour <strong>{{ $username }}</strong>,</p>

  <p>Votre compte a été supprimé le <strong>{{ now()->format('d/m/Y à H:i') }}</strong>.</p>

  <div style="background: #fff3e0; border-left: 4px solid #ff9800;
              padding: 15px; border-radius: 4px; margin: 20px 0;">
    <p style="margin: 0;">
      Vos données seront <strong>définitivement détruites le {{ $deletionDate }}</strong>.
    </p>
    <p style="margin: 8px 0 0;">
      Vous pouvez annuler cette suppression en vous reconnectant avant cette date.
    </p>
  </div>

  <p style="color: #666;">
    Si vous n'êtes pas à l'origine de cette demande, reconnectez-vous immédiatement
    et contactez notre support.
  </p>

  <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
  <p style="color: #999; font-size: 12px;">© {{ date('Y') }} GameFabrick</p>

</body>
</html>