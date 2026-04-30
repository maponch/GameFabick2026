<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;">

  <h2 style="color: #1976D2;">GameFabrick 🎲</h2>
  <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
  <p>Votre code de vérification est :</p>

  <div style="font-size: 36px; font-weight: bold; letter-spacing: 8px;
              background: #f5f5f5; padding: 20px; text-align: center;
              border-radius: 8px; margin: 20px 0;">
    {{ $otp }}
  </div>

  <p style="color: #666;">Ce code expire dans <strong>15 minutes</strong>.</p>
  <p style="color: #666;">Si vous n'avez pas fait cette demande, ignorez cet email.</p>

  <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
  <p style="color: #999; font-size: 12px;">© {{ date('Y') }} GameFabrick</p>

</body>
</html>
