<template>
  <v-dialog :model-value="modelValue" max-width="450" @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="pa-4">
      <v-card-title class="text-error">
        Supprimer mon compte ⚠️
      </v-card-title>

      <v-card-text>
        <v-alert type="warning" variant="tonal" density="compact" class="mb-4">
          Votre compte sera archivé pendant <strong>30 jours</strong> avant d'être supprimé définitivement.
          Vous pourrez annuler cette action en vous reconnectant pendant cette période.
        </v-alert>

        <p class="mb-4">
          Pour confirmer la suppression, entrez votre mot de passe :
        </p>

        <v-text-field v-model="password" label="Mot de passe" :type="showPassword ? 'text' : 'password'"
          prepend-inner-icon="mdi-lock" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showPassword = !showPassword" variant="outlined" :error-messages="errors.password"
          :disabled="saving" autocomplete="current-password" @keyup.enter="confirm" />
      </v-card-text>

      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="$emit('update:modelValue', false)">Annuler</v-btn>
        <v-btn color="error" :loading="saving" :disabled="!password" @click="confirm">
          Supprimer définitivement
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { api } from '../../api'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'deleted', 'error'])

const password = ref('')
const errors = ref({})
const saving = ref(false)
const showPassword = ref(false)

watch(() => props.modelValue, (val) => {
  if (val) {
    password.value = ''
    errors.value = {}
  }
})

async function confirm() {
  errors.value = {}

  if (!password.value) {
    errors.value.password = ['Le mot de passe est requis.']
    return
  }

  saving.value = true
  try {
    await api.delete('/user', {
      data: { password: password.value }
    })
    emit('deleted')
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      emit('error', 'Erreur lors de la suppression.')
    }
  } finally {
    saving.value = false
  }
}
</script>