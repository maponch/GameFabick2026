<template>
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-card>
      <v-card-title>
        Signaler {{ TYPE_LABELS[reportableType] }}
        <span v-if="targetLabel" class="text-body-2 text-medium-emphasis d-block mt-1">
          {{ targetLabel }}
        </span>
      </v-card-title>

      <v-card-text>
        <v-alert v-if="globalError" type="error" variant="tonal" density="compact" class="mb-3">
          {{ globalError }}
        </v-alert>

        <v-radio-group v-model="reasonCode" :error-messages="errors.reason_code" density="comfortable"
          hide-details="auto">
          <v-radio v-for="r in REASONS" :key="r.value" :label="r.label" :value="r.value" />
        </v-radio-group>

        <v-textarea v-model="reasonText"
          :label="isOther ? 'Précisez le motif (obligatoire)' : 'Précisions (facultatif)'"
          :error-messages="errors.reason_text" variant="outlined" rows="3" counter="1000" maxlength="1000"
          class="mt-3" />
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" :disabled="submitting" @click="dialog = false">
          Annuler
        </v-btn>
        <v-btn color="error" variant="flat" :loading="submitting" :disabled="!canSubmit" @click="submit">
          Signaler
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script setup>
import { ref, computed, watch } from 'vue'
import { api }from '../../api'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  reportableType: {
    type: String,
    required: true,
    validator: (v) => ['project', 'comment', 'template'].includes(v),
  },
  reportableId: { type: [Number, String], required: true },
  targetLabel: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue', 'reported'])

const REASONS = [
  { value: 'spam', label: 'Spam ou publicité' },
  { value: 'inappropriate', label: 'Contenu inapproprié' },
  { value: 'low_quality', label: 'Qualité insuffisante' },
  { value: 'copyright', label: 'Violation de droits d\'auteur' },
  { value: 'other', label: 'Autre' },
]

const TYPE_LABELS = {
  project: 'ce jeu',
  comment: 'ce commentaire',
  template: 'ce modèle de jeu',
}

const reasonCode = ref(null)
const reasonText = ref('')
const submitting = ref(false)
const errors = ref({})
const globalError = ref('')

const dialog = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const isOther = computed(() => reasonCode.value === 'other')

const canSubmit = computed(() => {
  if (!reasonCode.value) return false
  if (isOther.value && reasonText.value.trim() === '') return false
  return !submitting.value
})

const reset = () => {
  reasonCode.value = null
  reasonText.value = ''
  errors.value = {}
  globalError.value = ''
  submitting.value = false
}

watch(dialog, (open) => {
  if (open) reset()
})

const submit = async () => {
  submitting.value = true
  errors.value = {}
  globalError.value = ''

  try {
    await api.post('/reports', {
      reportable_type: props.reportableType,
      reportable_id: props.reportableId,
      reason_code: reasonCode.value,
      reason_text: reasonText.value.trim() || null,
    })

    emit('reported')
    dialog.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
      globalError.value = e.response.data.message || ''
    } else {
      globalError.value = 'Une erreur est survenue. Réessayez plus tard.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

