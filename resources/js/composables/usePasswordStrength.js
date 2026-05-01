import { computed, toValue } from 'vue'

export function usePasswordStrength(passwordRef) {
  const passwordStrength = computed(() => {
    const pwd = toValue(passwordRef)

    if (!pwd) return { score: 0, label: '', color: 'grey' }

    let score = 0
    if (pwd.length >= 8) score += 25
    if (pwd.length >= 12) score += 15
    if (/[A-Z]/.test(pwd)) score += 20
    if (/[0-9]/.test(pwd)) score += 20
    if (/[^A-Za-z0-9]/.test(pwd)) score += 20

    if (score <= 25) return { score, label: 'Très faible', color: 'error' }
    if (score <= 45) return { score, label: 'Faible', color: 'warning' }
    if (score <= 65) return { score, label: 'Moyen', color: 'yellow-darken-2' }
    if (score <= 80) return { score, label: 'Fort', color: 'success' }
    return { score, label: 'Très fort', color: 'green-darken-2' }
  })

  return { passwordStrength }
}