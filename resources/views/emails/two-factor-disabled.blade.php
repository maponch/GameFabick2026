<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;">

  <h2 style="color: #1976D2;">GameFabrick 🛡️</h2>

  <p>Bonjour <strong>{{ $username }}</strong>,</p>

  <p>
    Votre double authentification (2FA) a été <strong>désactivée</strong> par un administrateur
    à votre demande.
  </p>

  <div style="background: #fff3e0; border-left: 4px solid #ff9800;
              padding: 15px; border-radius: 4px; margin: 20px 0;">
    <p style="margin: 0 0 8px;"><strong>Raison fournie :</strong></p>
    <p style="margin: 0;">{{ $reason }}</p>
    <p style="margin: 12px 0 0; color: #666; font-size: 13px;">
      Action effectuée par : {{ $adminName }} le {{ now()->format('d/m/Y à H:i') }}
    </p>
  </div>

  <p style="color: #d32f2f;">
    <strong>Vous n'êtes pas à l'origine de cette demande ?</strong>
    Connectez-vous immédiatement, changez votre mot de passe et contactez le support.
  </p>

  <p>
    Pour votre sécurité, nous vous recommandons de
    <a href="{{ config('app.frontend_url') }}/login" style="color: #1976D2;">réactiver la 2FA</a>
    dès que possible.
  </p>

  <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
  <p style="color: #999; font-size: 12px;">© {{ date('Y') }} GameFabrick</p>

</body>
</html>