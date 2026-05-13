<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 20px;">

  <h2 style="color: #1976D2;">GameFabrick 🔑</h2>

  <p>Bonjour <strong>{{ $username }}</strong>,</p>

  <p>
    Vos codes de secours 2FA ont été régénérés à votre demande par {{ $adminName }}.
    Les anciens codes ne sont plus valides.
  </p>

  <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
    @foreach($codes as $code)
      <div style="font-family: monospace; font-size: 18px; letter-spacing: 2px;
                  padding: 6px 0; text-align: center;">
        {{ $code }}
      </div>
    @endforeach
  </div>

  <p style="color: #d32f2f;">
    <strong>Important :</strong> Conservez ces codes en lieu sûr. Chaque code ne peut être utilisé qu'une seule fois.
  </p>

  <p style="color: #666;">
    Si vous n'êtes pas à l'origine de cette demande, connectez-vous immédiatement et contactez le support.
  </p>

  <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
  <p style="color: #999; font-size: 12px;">© {{ date('Y') }} GameFabrick</p>

</body>
</html>