<template>
  <v-dialog v-model="dialog" max-width="560" persistent>
    <v-card>
      <v-card-title>{{ isEdit ? 'Modifier la carte' : 'Nouvelle carte' }}</v-card-title>

      <v-card-text>
        <v-form ref="formRef">
          <v-text-field v-model="form.name" label="Nom de la carte *" variant="outlined" :rules="[rules.required]"
            :error-messages="errors.name" class="mb-2" />

          <v-textarea v-model="form.description" label="Description" variant="outlined" rows="3"
            :error-messages="errors.description" class="mb-2" />

          <v-text-field v-model.number="form.quantity" type="number" label="Quantité *" variant="outlined"
            hint="Nombre d'exemplaires de cette carte dans le jeu" persistent-hint
            :rules="[rules.required, rules.positive]" :error-messages="errors.quantity" class="mb-4" />

          <div class="mb-2 text-body-2">Couleur de la carte</div>
          <div class="d-flex ga-4 align-start mb-4">
            <v-color-picker v-model="form.default_color" mode="hex" hide-inputs :modes="['hex']" />
            <div class="d-flex align-center ga-2">
              <div class="color-preview" :style="{ backgroundColor: form.default_color }" />
              <code>{{ form.default_color }}</code>
            </div>
          </div>

          <template v-if="cardSchema.length > 0">
            <v-divider class="mb-4" />
            <div class="text-subtitle-2 mb-3">Champs personnalisés</div>

            <template v-for="field in cardSchema" :key="field.key">
              <v-text-field v-if="field.type === 'text'" v-model="form.custom_data[field.key]"
                :label="field.label + (field.required ? ' *' : '')" variant="outlined" density="comfortable"
                :rules="field.required ? [rules.required] : []" class="mb-2" />

              <v-textarea v-else-if="field.type === 'textarea'" v-model="form.custom_data[field.key]"
                :label="field.label + (field.required ? ' *' : '')" variant="outlined" rows="2"
                :rules="field.required ? [rules.required] : []" class="mb-2" />

              <v-text-field v-else-if="field.type === 'number'" v-model="form.custom_data[field.key]" type="number"
                :label="field.label + (field.required ? ' *' : '')" variant="outlined" density="comfortable"
                :rules="numberRules(field)" class="mb-2" @keydown="blockExponent" />

              <v-select v-else-if="field.type === 'select'" v-model="form.custom_data[field.key]" :items="field.options"
                :label="field.label + (field.required ? ' *' : '')" variant="outlined" density="comfortable"
                :rules="field.required ? [rules.required] : []" clearable class="mb-2" />

              <v-switch v-else-if="field.type === 'boolean'" v-model="form.custom_data[field.key]" :label="field.label"
                color="primary" density="compact" class="mb-2" />

            </template>
          </template>

          <!-- BLOC MAPPING ICI, hors du v-if cardSchema -->
          <template v-if="supportsDeckMapping">
            <v-divider class="mb-4" />
            <div class="text-subtitle-2 mb-1">Correspondance jeu de cartes classique</div>
            <p class="text-caption text-medium-emphasis mb-3">
              Sélectionnez les cartes du jeu classique qui correspondront à cette carte
              en mode pense-bête.
            </p>
            <DeckMappingSelector v-model="form.existing_deck_mapping" />
          </template>

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
import { ref, computed, watch, watchEffect } from 'vue'
import { api } from '../../../api'
import DeckMappingSelector from './DeckMappingSelector.vue'


const props = defineProps({
  modelValue: { type: Boolean, default: false },
  templateId: { type: [String, Number], required: true },
  object: { type: Object, default: null },
  cardSchema: { type: Array, default: () => [] },
  templateFormats: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue', 'saved'])

const dialog = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v),
})

const isEdit = computed(() => !!props.object)

const formRef = ref(null)
const saving = ref(false)
const errors = ref({})

const supportsDeckMapping = computed(() =>
  props.templateFormats.includes('cartes-classiques')
)
watchEffect(() => {
  console.log('templateFormats:', props.templateFormats, '→ mapping:', supportsDeckMapping.value)
})

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
  positive: v => (v > 0) || 'Doit être supérieur à 0',
  isNumber: v => v === null || v === '' || !isNaN(Number(v)) || 'Doit être un nombre',
}

function blankForm() {
  return {
    name: '',
    description: '',
    quantity: 1,
    default_color: '#1976D2',
    custom_data: {},
    existing_deck_mapping: [],
  }
}

const form = ref(blankForm())

function initCustomData(existing = {}) {
  const data = {}
  for (const field of props.cardSchema) {
    if (field.key in existing) {
      data[field.key] = existing[field.key]
    } else {
      data[field.key] = field.type === 'boolean' ? false : null
    }
  }
  return data
}
function numberRules(field) {
  const r = [rules.isNumber]
  if (field.required) r.unshift(rules.required)
  return r
}

watch(dialog, (open) => {
  if (open) {
    errors.value = {}
    if (props.object) {
      form.value = {
        name: props.object.name,
        description: props.object.description ?? '',
        quantity: props.object.quantity,
        default_color: props.object.default_color || '#1976D2',
        custom_data: initCustomData(props.object.custom_data ?? {}),
        existing_deck_mapping: props.object.existing_deck_mapping ?? [],
      }
    } else {
      form.value = { ...blankForm(), custom_data: initCustomData() }
    }
  }
})

function close() {
  dialog.value = false
}
function blockExponent(e) {
  if (e.key === 'e' || e.key === 'E') {
    e.preventDefault()
  }
}

async function submit() {
  errors.value = {}
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    const cleanedCustomData = { ...form.value.custom_data }
      for (const field of props.cardSchema) {
        if (field.type === 'number') {
          const val = cleanedCustomData[field.key]
          cleanedCustomData[field.key] = (val === null || val === '') ? null : Number(val)
        }
    }

    const payload = {
      name: form.value.name,
      description: form.value.description,
      quantity: form.value.quantity,
      default_color: form.value.default_color,
      custom_data: cleanedCustomData,
      existing_deck_mapping: form.value.existing_deck_mapping,
    }

    if (isEdit.value) {
      await api.patch(`/admin/templates/${props.templateId}/objects/${props.object.id}`, payload)
    } else {
      await api.post(`/admin/templates/${props.templateId}/objects`, payload)
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

<style scoped>
.color-preview {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  border: 1px solid rgba(0, 0, 0, 0.2);
}
</style>