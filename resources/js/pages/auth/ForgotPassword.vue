<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Mot de passe oublié 🔑
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            Entrez votre email pour recevoir un code de vérification
          </v-card-subtitle>

          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
            @click:close="errorMessage = null">
            {{ errorMessage }}
          </v-alert>

          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </v-alert>

          <v-card-text>
            <v-text-field v-model="email" label="Email" type="email" prepend-inner-icon="mdi-email" variant="outlined"
              :error-messages="emailError" :disabled="loading || sent" autocomplete="email" class="mb-4"
              @keyup.enter="sendOtp" />

            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading || sent" @click="sendOtp">
              Envoyer le code
            </v-btn>
          </v-card-text>

          <v-card-actions class="justify-center flex-column ga-2">
            <v-btn v-if="sent" variant="text" color="primary" :disabled="cooldown > 0" @click="resend">
              {{ cooldown > 0 ? `Renvoyer dans ${cooldown}s` : 'Renvoyer le code' }}
            </v-btn>
            <v-btn variant="text" to="/login" size="small">
              Retour à la connexion
            </v-btn>
          </v-card-actions>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'

const router = useRouter()

const email = ref('')
const emailError = ref([])
const errorMessage = ref(null)
const successMessage = ref(null)
const loading = ref(false)
const sent = ref(false)
const cooldown = ref(0)

let cooldownTimer = null

function startCooldown() {
  cooldown.value = 60
  cooldownTimer = setInterval(() => {
    cooldown.value--
    if (cooldown.value <= 0) clearInterval(cooldownTimer)
  }, 1000)
}

async function sendOtp() {
  emailError.value = []
  errorMessage.value = null

  if (!email.value.trim()) {
    emailError.value = ['L\'email est requis.']
    return
  }

  loading.value = true
  try {
    await api.post('/forgot-password', { email: email.value.trim() })
    sent.value = true
    successMessage.value = 'Code envoyé ! Vérifiez votre boîte mail.'
    startCooldown()

    // Redirige vers la page de reset en passant l'email
    setTimeout(() => {
      router.push({ path: '/reset-password', query: { email: email.value.trim() } })
    }, 1500)

  } catch (e) {
    if (e.response?.status === 422) {
      emailError.value = e.response.data.errors?.email ?? []
    } else if (e.response?.status === 429) {
      errorMessage.value = 'Trop de tentatives. Veuillez patienter.'
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}

async function resend() {
  sent.value = false
  successMessage.value = null
  await sendOtp()
}

onUnmounted(() => clearInterval(cooldownTimer))
</script>