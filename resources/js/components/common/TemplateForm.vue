<template>
  <v-form ref="formRef">
    <v-text-field v-model="model.name" label="Nom du jeu *" variant="outlined" :readonly="readonly"
      :rules="[rules.required]" :error-messages="serverErrors.name" class="mb-2" />
    <v-select v-model="model.type_id" :items="types" item-title="name" item-value="id" label="Type de jeu *"
      variant="outlined" :readonly="readonly || lockTypeAndFormats" :loading="typesLoading" :rules="[rules.required]"
      :error-messages="serverErrors.type_id" :hint="lockTypeAndFormats ? 'Hérité du template d\'origine' : ''"
      persistent-hint class="mb-2" />
    <div class="mb-2">
      <label class="text-body-2 mb-2 d-block" :class="{ 'text-error': formatError }">
        Format(s) de jeu *
      </label>

      <div v-if="formatsLoading" class="d-flex justify-center pa-3">
        <v-progress-circular indeterminate size="24" color="primary" />
      </div>
      <div v-else class="d-flex flex-wrap ga-2">
        <v-chip v-for="f in formats" :key="f.id" :color="model.format_ids?.includes(f.id) ? 'primary' : ''"
          :variant="model.format_ids?.includes(f.id) ? 'flat' : 'outlined'"
          :prepend-icon="model.format_ids?.includes(f.id) ? 'mdi-check' : 'mdi-plus'"
          :disabled="readonly || lockTypeAndFormats" class="cursor-pointer" @click="toggleFormat(f.id)">
          {{ f.name }}
        </v-chip>
      </div>

      <div v-if="lockTypeAndFormats" class="text-caption text-medium-emphasis mt-1">
        Hérité du template d'origine
      </div>
      <div v-if="formatError" class="text-error text-caption mt-1">
        Sélectionnez au moins un format
      </div>
      <div v-if="serverErrors.format_ids" class="text-error text-caption mt-1">
        {{ Array.isArray(serverErrors.format_ids) ? serverErrors.format_ids[0] : serverErrors.format_ids }}
      </div>
    </div>
    <v-textarea v-model="model.description" label="Description" variant="outlined" :readonly="readonly" rows="2"
      :error-messages="serverErrors.description" class="mb-2" />
    <v-textarea v-model="model.rules" label="Règles" variant="outlined" :readonly="readonly" rows="4"
      :error-messages="serverErrors.rules" class="mb-2" />
    <v-row>
      <v-col cols="6">
        <v-text-field v-model.number="model.min_players" type="number" label="Joueurs min *" variant="outlined"
          :disabled="readonly" :rules="[rules.required, rules.positive]" :error-messages="serverErrors.min_players" />
      </v-col>
      <v-col cols="6">
        <v-text-field v-model.number="model.max_players" type="number" label="Joueurs max *" variant="outlined"
          :disabled="readonly" :rules="[rules.required, rules.positive, rules.maxGteMin]"
          :error-messages="serverErrors.max_players" />
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="6">
        <v-text-field v-model.number="model.duration_min" type="number" label="Durée min (min) *" variant="outlined"
          :disabled="readonly" :rules="[rules.required, rules.positive]" :error-messages="serverErrors.duration_min" />
      </v-col>
      <v-col cols="6">
        <v-text-field v-model.number="model.duration_max" type="number" label="Durée max (min) *" variant="outlined"
          :disabled="readonly" :rules="[rules.required, rules.positive, rules.durMaxGteMin]"
          :error-messages="serverErrors.duration_max" />
      </v-col>
    </v-row>
  </v-form>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  types: { type: Array, default: () => [] },
  typesLoading: { type: Boolean, default: false },
  formats: { type: Array, default: () => [] },
  formatsLoading: { type: Boolean, default: false },
  serverErrors: { type: Object, default: () => ({}) },
  readonly: { type: Boolean, default: false },
  context: { type: String, default: 'admin' },
  inheritedFromTemplate: { type: Boolean, default: false },
})
const model = defineModel({ required: true })
const formRef = ref(null)

const formatsTouched = ref(false)

const formatError = computed(() =>
  formatsTouched.value
  && (!Array.isArray(model.value.format_ids) || model.value.format_ids.length === 0)
)

const lockTypeAndFormats = computed(() =>
  props.context === 'user' && props.inheritedFromTemplate
)

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
  positive: v => (v > 0) || 'Doit être supérieur à 0',
  maxGteMin: v => (v >= model.value.min_players) || 'Doit être ≥ joueurs min',
  durMaxGteMin: v => (v >= model.value.duration_min) || 'Doit être ≥ durée min',
  atLeastOne: v => (Array.isArray(v) && v.length > 0) || 'Sélectionnez au moins un format',
}
function toggleFormat(id) {
  if (props.readonly || lockTypeAndFormats.value) return
  formatsTouched.value = true
  if (!Array.isArray(model.value.format_ids)) {
    model.value.format_ids = []
  }
  const idx = model.value.format_ids.indexOf(id)
  if (idx === -1) model.value.format_ids.push(id)
  else model.value.format_ids.splice(idx, 1)
}

async function validate() {
  const { valid } = await formRef.value.validate()
  const formatsValid = Array.isArray(model.value.format_ids) && model.value.format_ids.length > 0
  if (!formatsValid) formatsTouched.value = true
  return valid && formatsValid
}

defineExpose({ validate })
</script>
<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>