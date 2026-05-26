<template>
  <v-form ref="formRef">
    <v-text-field v-model="model.name" label="Nom du jeu *" variant="outlined" :rules="[rules.required]"
      :error-messages="serverErrors.name" class="mb-2" />

    <v-select v-model="model.type_id" :items="types" item-title="name" item-value="id" label="Type de jeu *"
      variant="outlined" :loading="typesLoading" :rules="[rules.required]" :error-messages="serverErrors.type_id"
      class="mb-2" />

    <v-textarea v-model="model.description" label="Description" variant="outlined" rows="2"
      :error-messages="serverErrors.description" class="mb-2" />

    <v-textarea v-model="model.rules" label="Règles" variant="outlined" rows="4" :error-messages="serverErrors.rules"
      class="mb-2" />

    <v-row>
      <v-col cols="6">
        <v-text-field v-model.number="model.min_players" type="number" label="Joueurs min *" variant="outlined"
          :rules="[rules.required, rules.positive]" :error-messages="serverErrors.min_players" />
      </v-col>
      <v-col cols="6">
        <v-text-field v-model.number="model.max_players" type="number" label="Joueurs max *" variant="outlined"
          :rules="[rules.required, rules.positive, rules.maxGteMin]" :error-messages="serverErrors.max_players" />
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="6">
        <v-text-field v-model.number="model.duration_min" type="number" label="Durée min (min) *" variant="outlined"
          :rules="[rules.required, rules.positive]" :error-messages="serverErrors.duration_min" />
      </v-col>
      <v-col cols="6">
        <v-text-field v-model.number="model.duration_max" type="number" label="Durée max (min) *" variant="outlined"
          :rules="[rules.required, rules.positive, rules.durMaxGteMin]" :error-messages="serverErrors.duration_max" />
      </v-col>
    </v-row>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'

const model = defineModel({ required: true })

const props = defineProps({
  types: { type: Array, default: () => [] },
  typesLoading: { type: Boolean, default: false },
  serverErrors: { type: Object, default: () => ({}) },
})

const formRef = ref(null)

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
  positive: v => (v > 0) || 'Doit être supérieur à 0',
  maxGteMin: v => (v >= model.value.min_players) || 'Doit être ≥ joueurs min',
  durMaxGteMin: v => (v >= model.value.duration_min) || 'Doit être ≥ durée min',
}

async function validate() {
  const { valid } = await formRef.value.validate()
  return valid
}

defineExpose({ validate })
</script>