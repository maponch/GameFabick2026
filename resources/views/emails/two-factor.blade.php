<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;">

  <h2 style="color: #1976D2;">GameFabrick 🔐</h2>
  <p>Vous avez activé l'authentification à deux facteurs.</p>
  <p>Pour finaliser la connexion, entrez ce code :</p>

  <div style="font-size: 36px; font-weight: bold; letter-spacing: 8px;
              background: #f5f5f5; padding: 20px; text-align: center;
              border-radius: 8px; margin: 20px 0;">
    {{ $otp }}
  </div>

  <p style="color: #666;">Ce code expire dans <strong>10 minutes</strong>.</p>
  <p style="color: #d32f2f;">
    Si vous n'êtes pas à l'origine de cette tentative, changez immédiatement votre mot de passe.
  </p>

  <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
  <p style="color: #999; font-size: 12px;">© {{ date('Y') }} GameFabrick</p>

</body>
</html>