<template>
  <v-dialog :model-value="modelValue" max-width="400" eager @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="pa-4">
      <v-card-title>Modifier le mot de passe</v-card-title>
      <v-card-text>
        <v-text-field v-model="form.current_password" label="Mot de passe actuel"
          :type="showCurrent ? 'text' : 'password'" :append-inner-icon="showCurrent ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showCurrent = !showCurrent" variant="outlined" :error-messages="errors.current_password"
          :disabled="saving" class="mb-2" />
        <v-text-field v-model="form.password" label="Nouveau mot de passe" :type="showNew ? 'text' : 'password'"
          :append-inner-icon="showNew ? 'mdi-eye-off' : 'mdi-eye'" @click:append-inner="showNew = !showNew"
          @update:model-value="val => form.password = val" variant="outlined" :error-messages="errors.password"
          :disabled="saving" class="mb-1" />
        <div v-if="form.password" class="mb-4">
          <div class="text-caption mb-1">
            Force : <strong :class="passwordStrength.color">{{ passwordStrength.label }}</strong>
          </div>
          <v-progress-linear :model-value="passwordStrength.score" :color="passwordStrength.color" height="4" rounded />
        </div>
        <v-text-field v-model="form.password_confirmation" label="Confirmer le mot de passe"
          :type="showConfirm ? 'text' : 'password'" :append-inner-icon="showConfirm ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showConfirm = !showConfirm" variant="outlined"
          :error-messages="errors.password_confirmation" :disabled="saving" />
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="$emit('update:modelValue', false)">Annuler</v-btn>
        <v-btn color="primary" :loading="saving" @click="save">Enregistrer</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { api } from '../../api'
import { usePasswordStrength } from '../../composables/usePasswordStrength' 
import { usePasswordValidation } from '../../composables/usePasswordValidation'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'updated', 'error'])

const form = ref({ current_password: '', password: '', password_confirmation: '' })
const errors = ref({})
const saving = ref(false)
const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const { passwordStrength } = usePasswordStrength(() => form.value.password)
const { validatePassword } = usePasswordValidation(() => form.value.password)

watch(() => props.modelValue, (val) => {
  if (val) {
    form.value = { current_password: '', password: '', password_confirmation: '' }
    errors.value = {}
  }
})

async function save() {
  errors.value = {}

  const passwordError = validatePassword()
  if (passwordError) {
    errors.value.password = [passwordError]
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = ['Les mots de passe ne correspondent pas.']
    return
  }

  saving.value = true
  try {
    await api.patch('/user/password', form.value)
    emit('updated')
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else emit('error', 'Erreur lors de la mise à jour.')
  } finally {
    saving.value = false
  }
}
</script>