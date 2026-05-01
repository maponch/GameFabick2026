<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <!-- ÉTAPE 1 : Code OTP -->
          <template v-if="step === 1">

            <v-card-title class="text-h5 text-center mb-2">
              Vérification 🔑
            </v-card-title>
            <v-card-subtitle class="text-center mb-4">
              Entrez le code à 6 chiffres reçu par email
            </v-card-subtitle>

            <v-alert v-if="errors.otp" type="error" variant="tonal" class="mb-4">
              {{ errors.otp[0] }}
            </v-alert>

            <v-card-text>
              <div class="d-flex ga-2 mb-6 justify-center">
                <v-text-field v-for="(_, i) in otp" :key="i" :ref="el => otpRefs[i] = el" v-model="otp[i]" maxlength="1"
                  variant="outlined" style="width: 48px" class="text-center" :error="!!errors.otp" hide-details
                  @input="onOtpInput(i)" @keydown.backspace="onOtpBackspace(i)" @paste.prevent="onOtpPaste($event)" />
              </div>

              <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="verifyOtp">
                Vérifier le code
              </v-btn>
            </v-card-text>

            <v-card-actions class="justify-center">
              <v-btn variant="text" to="/forgot-password" size="small">
                Renvoyer un code
              </v-btn>
            </v-card-actions>

          </template>

          <!-- ÉTAPE 2 : Nouveau mot de passe -->
          <template v-if="step === 2">

            <v-card-title class="text-h5 text-center mb-2">
              Nouveau mot de passe 🔐
            </v-card-title>
            <v-card-subtitle class="text-center mb-4">
              Choisissez un nouveau mot de passe sécurisé
            </v-card-subtitle>

            <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
              @click:close="errorMessage = null">
              {{ errorMessage }}
            </v-alert>

            <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
              {{ successMessage }}
            </v-alert>

            <v-card-text>
              <v-text-field v-model="form.password" label="Nouveau mot de passe"
                :type="showPassword ? 'text' : 'password'" prepend-inner-icon="mdi-lock"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword" 
                @update:model-value="val => form.password = val"
                variant="outlined" :error-messages="errors.password"
                :disabled="loading" autocomplete="new-password" class="mb-2" />
              <div v-if="form.password" class="mb-3">
                <div class="text-caption mb-1">
                  Force : <strong :class="passwordStrength.color">{{ passwordStrength.label }}</strong>
                </div>
                <v-progress-linear :model-value="passwordStrength.score" :color="passwordStrength.color" height="4"
                  rounded />
              </div>

              <v-text-field v-model="form.password_confirmation" label="Confirmer le mot de passe"
                :type="showConfirm ? 'text' : 'password'" prepend-inner-icon="mdi-lock-check"
                :append-inner-icon="showConfirm ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showConfirm = !showConfirm" variant="outlined"
                :error-messages="errors.password_confirmation" :disabled="loading" autocomplete="new-password"
                class="mb-4" />

              <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="resetPassword">
                Réinitialiser le mot de passe
              </v-btn>
            </v-card-text>

          </template>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api } from '../../api'
import { usePasswordStrength } from '../../composables/usePasswordStrength'
import { usePasswordValidation } from '../../composables/usePasswordValidation'

const router = useRouter()
const route = useRoute()

const email = ref(route.query.email ?? '')
const step = ref(1)

const otp = ref(['', '', '', '', '', ''])
const otpRefs = ref([])
const otpString = ref('')

const form = ref({ password: '', password_confirmation: '' })
const errors = ref({})
const errorMessage = ref(null)
const successMessage = ref(null)
const loading = ref(false)
const showPassword = ref(false)
const showConfirm = ref(false)

const { passwordStrength } = usePasswordStrength(() => form.value.password)
const { validatePassword } = usePasswordValidation(() => form.value.password)

onMounted(() => otpRefs.value[0]?.focus())

function onOtpInput(index) {
  otp.value[index] = otp.value[index].replace(/\D/g, '')
  if (otp.value[index] && index < 5) {
    otpRefs.value[index + 1]?.focus()
  }
  // Auto-vérifie quand les 6 chiffres sont saisis
  if (otp.value.every(d => d !== '')) verifyOtp()
}

function onOtpBackspace(index) {
  if (!otp.value[index] && index > 0) {
    otp.value[index - 1] = ''
    otpRefs.value[index - 1]?.focus()
  }
}

function onOtpPaste(e) {
  const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
  pasted.split('').forEach((char, i) => { if (i < 6) otp.value[i] = char })
  otpRefs.value[Math.min(pasted.length, 5)]?.focus()
  if (pasted.length === 6) verifyOtp()
}

async function verifyOtp() {
  errors.value = {}
  otpString.value = otp.value.join('')

  if (otpString.value.length < 6) {
    errors.value.otp = ['Veuillez entrer le code complet à 6 chiffres.']
    return
  }

  loading.value = true
  try {
    // Vérifie le code sans réinitialiser le mot de passe
    await api.post('/verify-otp', {
      email: email.value,
      otp: otpString.value,
    })
    step.value = 2 // ✅ passe à l'étape 2 seulement si le code est valide
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      errors.value.otp = ['Une erreur est survenue. Veuillez réessayer.']
    }
  } finally {
    loading.value = false
  }
}

async function resetPassword() {
  errors.value = {}
  errorMessage.value = null

  if (!form.value.password) {
    errors.value.password = ['Le mot de passe est requis.']
    return
  }
  
  const passwordError = validatePassword()
  if (passwordError) {
    errors.value.password = [passwordError]
    return
  }


  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = ['Les mots de passe ne correspondent pas.']
    return
  }

  loading.value = true
  try {
    await api.post('/reset-password', {
      email: email.value,
      otp: otpString.value,   // ✅ réutilise le code déjà vérifié
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    })

    successMessage.value = 'Mot de passe réinitialisé ! Redirection...'
    setTimeout(() => router.push('/login'), 1500)

  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}
</script>