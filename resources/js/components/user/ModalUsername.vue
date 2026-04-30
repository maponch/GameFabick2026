<template>
  <v-dialog :model-value="modelValue" max-width="400" @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="pa-4">
      <v-card-title>Modifier le pseudo</v-card-title>
      <v-card-text>
        <v-text-field v-model="form.username" label="Nouveau pseudo" variant="outlined"
          :error-messages="errors.username" :disabled="saving" @keyup.enter="save" />
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

const props = defineProps({
  modelValue: Boolean,
  currentUsername: String,
})

const emit = defineEmits(['update:modelValue', 'updated', 'error'])

const form = ref({ username: '' })
const errors = ref({})
const saving = ref(false)

// Pré-remplit à chaque ouverture
watch(() => props.modelValue, (val) => {
  if (val) {
    form.value.username = props.currentUsername
    errors.value = {}
  }
})

async function save() {
  errors.value = {}
  saving.value = true
  try {
    const { data } = await api.patch('/user/username', form.value)
    emit('updated', data.user)
    emit('update:modelValue', false)
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else emit('error', 'Erreur lors de la mise à jour.')
  } finally {
    saving.value = false
  }
}
</script>