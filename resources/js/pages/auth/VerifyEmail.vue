<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Vérification email 📧
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            Un code à 6 chiffres a été envoyé à <strong>{{ userEmail }}</strong>
          </v-card-subtitle>

          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
            @click:close="errorMessage = null">
            {{ errorMessage }}
          </v-alert>

          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </v-alert>

          <v-card-text>

            <div class="text-caption text-medium-emphasis mb-2 text-center">
              Code à 6 chiffres
            </div>

            <div class="d-flex ga-2 mb-6 justify-center">
              <v-text-field v-for="(_, i) in otp" :key="i" :ref="el => otpRefs[i] = el" v-model="otp[i]" maxlength="1"
                variant="outlined" style="width: 48px" class="text-center" :error="!!errors.otp" hide-details
                @input="onOtpInput(i)" @keydown.backspace="onOtpBackspace(i)" @paste.prevent="onOtpPaste($event)" />
            </div>

            <div v-if="errors.otp" class="text-error text-caption mb-4 text-center">
              {{ errors.otp[0] }}
            </div>

            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="verify">
              Vérifier
            </v-btn>

          </v-card-text>

          <v-card-actions class="justify-center">
            <v-btn variant="text" color="primary" size="small" :disabled="cooldown > 0" @click="resend">
              {{ cooldown > 0 ? `Renvoyer dans ${cooldown}s` : 'Renvoyer le code' }}
            </v-btn>
          </v-card-actions>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'
import { getUser, clearUser } from '../../router'

const router = useRouter()

const userEmail = ref('')
const otp = ref(['', '', '', '', '', ''])
const otpRefs = ref([])
const errors = ref({})
const errorMessage = ref(null)
const successMessage = ref(null)
const loading = ref(false)
const cooldown = ref(0)
let cooldownTimer = null

onMounted(async () => {
  const user = await getUser()
  if (!user) { router.push('/login'); return }
  if (user.email_verified_at) { router.push('/dashboard'); return }
  userEmail.value = user.email
  otpRefs.value[0]?.focus()
})

onUnmounted(() => clearInterval(cooldownTimer))

function startCooldown() {
  cooldown.value = 60
  cooldownTimer = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) clearInterval(cooldownTimer)
  }, 1000)
}

function onOtpInput(index) {
  otp.value[index] = otp.value[index].replace(/\D/g, '')
  if (otp.value[index] && index < 5) otpRefs.value[index + 1]?.focus()
  if (otp.value.every(d => d !== '')) verify()
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
  if (pasted.length === 6) verify()
}

async function verify() {
  errors.value = {}
  const otpString = otp.value.join('')

  if (otpString.length < 6) {
    errors.value.otp = ['Veuillez entrer le code complet.']
    return
  }

  loading.value = true
  try {
    await api.post('/email/verify', { otp: otpString })
    clearUser() // ✅ force le rechargement du user avec email_verified_at à jour
    successMessage.value = 'Email vérifié ! Redirection...'
    setTimeout(() => router.push('/dashboard'), 1500)
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    // Reset OTP
    otp.value = ['', '', '', '', '', '']
    otpRefs.value[0]?.focus()
  } finally {
    loading.value = false
  }
}

async function resend() {
  errorMessage.value = null
  try {
    await api.post('/email/send-otp')
    startCooldown()
    successMessage.value = 'Nouveau code envoyé !'
    setTimeout(() => successMessage.value = null, 3000)
  } catch {
    errorMessage.value = 'Erreur lors de l\'envoi. Veuillez réessayer.'
  }
}
</script>