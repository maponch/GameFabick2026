export function usePasswordValidation(password) {
  const rules = [
    { test: (p) => p.length >= 8, message: 'Le mot de passe doit faire au moins 8 caractères.' },
    { test: (p) => /[A-Z]/.test(p), message: 'Le mot de passe doit contenir au moins une majuscule.' },
    { test: (p) => /[0-9]/.test(p), message: 'Le mot de passe doit contenir au moins un chiffre.' },
    { test: (p) => /[^A-Za-z0-9]/.test(p), message: 'Le mot de passe doit contenir au moins un caractère spécial.' },
  ]

  function validatePassword() {
    for (const rule of rules) {
      if (!rule.test(password())) return rule.message
    }
    return null
  }

  return { validatePassword }
}