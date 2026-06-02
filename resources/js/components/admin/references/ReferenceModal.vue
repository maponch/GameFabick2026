<template>
  <v-dialog v-model="dialog" max-width="480" persistent>
    <v-card>
      <v-card-title>{{ isEdit ? `Modifier : ${entityLabel}` : `Nouveau : ${entityLabel}` }}</v-card-title>

      <v-card-text>
        <v-form ref="formRef">
          <v-text-field v-model="form.name" label="Nom *" variant="outlined" autofocus :rules="[rules.required]"
            :error-messages="errors.name" />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" :disabled="saving" @click="close">Annuler</v-btn>
        <v-btn color="primary" :loading="saving" @click="submit">
          {{ isEdit ? 'Enregistrer' : 'Créer' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { api } from '../../../api'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  entity: { type: Object, default: null },
  endpoint: { type: String, required: true },
  entityLabel: { type: String, required: true },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const dialog = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v),
})

const isEdit = computed(() => !!props.entity)

const formRef = ref(null)
const saving = ref(false)
const errors = ref({})

const form = ref({ name: '' })

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
}

watch(dialog, (open) => {
  if (open) {
    errors.value = {}
    form.value.name = props.entity?.name ?? ''
  }
})

function close() {
  dialog.value = false
}

async function submit() {
  errors.value = {}
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    if (isEdit.value) {
      await api.patch(`${props.endpoint}/${props.entity.id}`, form.value)
    } else {
      await api.post(props.endpoint, form.value)
    }
    emit('saved')
    dialog.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    }
  } finally {
    saving.value = false
  }
}
</script>