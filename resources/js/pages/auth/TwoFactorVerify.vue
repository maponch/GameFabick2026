<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Vérification 2FA 🔐
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            <template v-if="method === 'totp'">
              Entrez le code de votre application authentificateur
            </template>
            <template v-else>
              Un code a été envoyé à votre email
            </template>
          </v-card-subtitle>

          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4">
            {{ errorMessage }}
          </v-alert>

          <v-card-text>

            <div class="d-flex ga-2 mb-4 justify-center">
              <v-text-field v-for="(_, i) in code" :key="i" :ref="el => codeRefs[i] = el" v-model="code[i]"
                maxlength="1" variant="outlined" style="width: 48px" class="text-center" :error="!!errors.code"
                hide-details @input="onCodeInput(i)" @keydown.backspace="onCodeBackspace(i)"
                @paste.prevent="onCodePaste($event)" />
            </div>

            <div v-if="errors.code" class="text-error text-caption mb-4 text-center">
              {{ errors.code[0] }}
            </div>

            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="verify">
              Vérifier
            </v-btn>

            <div class="text-center mt-4">
              <v-btn variant="text" size="small" color="primary" @click="useRecovery = !useRecovery">
                {{ useRecovery ? 'Utiliser le code 2FA' : 'Utiliser un code de secours' }}
              </v-btn>
            </div>

            <v-text-field v-if="useRecovery" v-model="recoveryCode" label="Code de secours" variant="outlined"
              prepend-inner-icon="mdi-key" class="mt-2" @keyup.enter="verifyRecovery" />

            <v-btn v-if="useRecovery" block color="warning" :loading="loading" @click="verifyRecovery">
              Utiliser ce code de secours
            </v-btn>

          </v-card-text>

          <v-card-actions class="justify-center">
            <v-btn variant="text" to="/login" size="small">
              Retour à la connexion
            </v-btn>
          </v-card-actions>
          <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
            {{ snackbar.message }}
          </v-snackbar>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api } from '../../api'
import { clearUser } from '../../router'

const router = useRouter()
const route = useRoute()

const email = ref(route.query.email ?? '')
const method = ref(route.query.method ?? 'totp')

const code = ref(['', '', '', '', '', ''])
const codeRefs = ref([])
const recoveryCode = ref('')
const useRecovery = ref(false)

const errors = ref({})
const errorMessage = ref(null)
const loading = ref(false)

const snackbar = ref({ show: false, message: '', color: 'warning' })


onMounted(() => {
  if (!email.value) router.push('/login')
  else codeRefs.value[0]?.focus()
})

function onCodeInput(index) {
  code.value[index] = code.value[index].replace(/\D/g, '')
  if (code.value[index] && index < 5) codeRefs.value[index + 1]?.focus()
  if (code.value.every(d => d !== '')) verify()
}

function onCodeBackspace(index) {
  if (!code.value[index] && index > 0) {
    code.value[index - 1] = ''
    codeRefs.value[index - 1]?.focus()
  }
}

function onCodePaste(e) {
  const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
  pasted.split('').forEach((char, i) => { if (i < 6) code.value[i] = char })
  codeRefs.value[Math.min(pasted.length, 5)]?.focus()
  if (pasted.length === 6) verify()
}

async function verify() {
  errors.value = {}
  errorMessage.value = null

  const codeString = code.value.join('')
  if (codeString.length < 6) {
    errors.value.code = ['Veuillez entrer le code complet.']
    return
  }

  await sendVerification(codeString)
}

async function verifyRecovery() {
  errors.value = {}
  errorMessage.value = null

  if (!recoveryCode.value) {
    errors.value.code = ['Le code de secours est requis.']
    return
  }

  await sendVerification(recoveryCode.value)
}

async function sendVerification(codeValue) {
  loading.value = true
  try {
    const { data } = await api.post('/2fa/verify', {
      email: email.value,
      code: codeValue,
    })

    clearUser()
    
    if (data.used_recovery_code) {
      snackbar.value = {
        show: true,
        message: `Code de secours utilisé. Il vous reste ${data.remaining_recovery_codes} code(s).`,
        color: 'warning'
      }

      // Délai court pour laisser le temps de voir le message
      setTimeout(() => {
        if (data.user?.role === 'admin') router.push('/admin')
        else router.push('/dashboard')
      }, 4000)
      return
    }

    if (data.user?.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/dashboard')
    }

  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      errorMessage.value = 'Une erreur est survenue.'
    }
    code.value = ['', '', '', '', '', '']
    codeRefs.value[0]?.focus()
  } finally {
    loading.value = false
  }
}
</script>