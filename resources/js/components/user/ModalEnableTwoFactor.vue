<template>
  <v-dialog :model-value="modelValue" max-width="500" @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="pa-4">
      <v-card-title>Activer la 2FA 🔐</v-card-title>

      <v-card-text>

        <!-- Étape 1 : choix de la méthode -->
        <template v-if="step === 1">
          <p class="mb-4">Choisissez votre méthode :</p>

          <v-card class="pa-4 mb-3 cursor-pointer" :variant="selectedMethod === 'totp' ? 'tonal' : 'outlined'"
            :color="selectedMethod === 'totp' ? 'primary' : ''" @click="selectedMethod = 'totp'">
            <div class="d-flex align-center">
              <v-icon size="40" class="me-3">mdi-cellphone-key</v-icon>
              <div>
                <div class="font-weight-bold">Application authentificateur</div>
                <div class="text-caption">Google Authenticator, Authy, etc.</div>
              </div>
            </div>
          </v-card>

          <v-card class="pa-4 cursor-pointer" :variant="selectedMethod === 'email' ? 'tonal' : 'outlined'"
            :color="selectedMethod === 'email' ? 'primary' : ''" @click="selectedMethod = 'email'">
            <div class="d-flex align-center">
              <v-icon size="40" class="me-3">mdi-email-lock</v-icon>
              <div>
                <div class="font-weight-bold">Code par email</div>
                <div class="text-caption">Code reçu à chaque connexion</div>
              </div>
            </div>
          </v-card>
        </template>

        <!-- Étape 2 TOTP : QR code -->
        <template v-if="step === 2 && selectedMethod === 'totp'">
          <p class="mb-3">
            Scannez ce QR code avec votre application authentificateur :
          </p>
          <div class="text-center mb-4" v-html="qrCode"></div>
          <p class="text-caption text-medium-emphasis mb-2">
            Si vous ne pouvez pas scanner, entrez ce code manuellement :
          </p>
          <v-text-field :model-value="secret" variant="outlined" readonly density="compact" class="mb-3" />
        </template>

        <!-- Étape 2 email : confirmation envoi -->
        <template v-if="step === 2 && selectedMethod === 'email'">
          <v-alert type="info" variant="tonal" class="mb-4">
            Un code à 6 chiffres a été envoyé à votre email.
          </v-alert>
        </template>

        <!-- Étape 3 : vérification -->
        <template v-if="step === 2">
          <p class="mb-2">Entrez le code à 6 chiffres :</p>
          <v-text-field v-model="verificationCode" label="Code" variant="outlined" maxlength="6"
            :error-messages="errors.code" @keyup.enter="enable" />
        </template>

        <!-- Étape 3 : codes de secours -->
        <template v-if="step === 3">
          <v-alert type="success" variant="tonal" class="mb-4">
            <strong>2FA activée !</strong> Conservez ces codes de secours en lieu sûr.
            Ils ne seront affichés qu'une seule fois.
          </v-alert>

          <v-card variant="tonal" class="pa-3 mb-3">
            <div class="d-flex flex-wrap ga-2">
              <v-chip v-for="code in recoveryCodes" :key="code" size="small" variant="outlined">
                {{ code }}
              </v-chip>
            </div>
          </v-card>

          <v-btn block variant="tonal" prepend-icon="mdi-content-copy" @click="copyRecoveryCodes">
            Copier les codes
          </v-btn>
        </template>

      </v-card-text>

      <v-card-actions class="justify-end">
        <v-btn v-if="step < 3" variant="text" @click="$emit('update:modelValue', false)">
          Annuler
        </v-btn>

        <v-btn v-if="step === 1" color="primary" :disabled="!selectedMethod" @click="goToStep2">
          Continuer
        </v-btn>

        <v-btn v-if="step === 2" color="primary" :loading="loading" @click="enable">
          Activer
        </v-btn>

        <v-btn v-if="step === 3" color="primary" @click="finish">
          Terminé
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { api } from '../../api'

const props = defineProps({ modelValue: Boolean })
const emit = defineEmits(['update:modelValue', 'enabled','email-sent'])

const step = ref(1)
const selectedMethod = ref(null)
const qrCode = ref('')
const secret = ref('')
const verificationCode = ref('')
const recoveryCodes = ref([])
const errors = ref({})
const loading = ref(false)

watch(() => props.modelValue, (val) => {
  if (val) {
    step.value = 1
    selectedMethod.value = null
    verificationCode.value = ''
    errors.value = {}
  }
})

async function goToStep2() {
  loading.value = true
  try {
    if (selectedMethod.value === 'totp') {
      const { data } = await api.post('/2fa/generate-totp')
      qrCode.value = data.qr_code
      secret.value = data.secret
    } else {
      await api.post('/2fa/send-email')
      emit('email-sent')
    }
    step.value = 2
  } catch {
    errors.value.code = ['Erreur lors de la génération.']
  } finally {
    loading.value = false
  }
}

async function enable() {
  errors.value = {}
  if (!verificationCode.value) {
    errors.value.code = ['Code requis.']
    return
  }

  loading.value = true
  try {
    const { data } = await api.post('/2fa/enable', {
      method: selectedMethod.value,
      code: verificationCode.value,
    })
    recoveryCodes.value = data.recovery_codes
    step.value = 3
    emit('enabled')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
  } finally {
    loading.value = false
  }
}

function copyRecoveryCodes() {
  navigator.clipboard.writeText(recoveryCodes.value.join('\n'))
}

function finish() {
  emit('update:modelValue', false)
}
</script>