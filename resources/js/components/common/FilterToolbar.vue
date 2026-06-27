<template>
  <v-card class="mb-4">
    <div class="pa-3 d-flex align-center justify-space-between cursor-pointer" @click="expanded = !expanded">
      <div class="d-flex align-center ga-2">
        <v-icon>mdi-filter-variant</v-icon>
        <span class="font-weight-medium">Filtres</span>
        <v-chip v-if="activeCount > 0" size="x-small" color="primary" variant="tonal">
          {{ activeCount }}
        </v-chip>
      </div>
      <div class="d-flex align-center ga-1">
        <v-btn v-if="activeCount > 0" variant="text" size="small" prepend-icon="mdi-close" @click.stop="reset">
          Réinitialiser
        </v-btn>
        <v-icon>{{ expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
      </div>
    </div>

    <v-expand-transition>
      <div v-show="expanded" class="px-3 pb-3">
        <v-divider class="mb-3" />
        <div class="d-flex flex-wrap ga-3 align-end">
          <template v-for="f in filters" :key="f.key">
            <v-text-field v-if="f.type === 'text'" :model-value="modelValue[f.key]" :label="f.label"
              :prepend-inner-icon="f.icon ?? 'mdi-magnify'" variant="outlined" density="compact" hide-details clearable
              :style="`max-width: ${f.width ?? 280}px; min-width: 200px;`"
              @update:model-value="updateField(f.key, $event)" />
            <v-select v-else-if="f.type === 'select'" :model-value="modelValue[f.key]" :label="f.label"
              :items="f.options" variant="outlined" density="compact" hide-details clearable
              :style="`max-width: ${f.width ?? 200}px; min-width: 180px;`"
              @update:model-value="updateField(f.key, $event)" />
            <div v-else-if="f.type === 'toggle'" class="d-flex align-center ga-2" style="height: 40px;">
              <span class="text-body-2">{{ f.label }}</span>
              <v-switch :model-value="modelValue[f.key] === true" color="primary" density="compact" hide-details inset
                class="flex-grow-0" style="transform: scale(0.85);"
                @update:model-value="updateField(f.key, $event ? true : null)" />
            </div>
          </template>
        </div>
      </div>
    </v-expand-transition>
  </v-card>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: { type: Object, required: true },
  filters: { type: Array, required: true },
  defaults: { type: Object, default: () => ({}) },
})
const expanded = ref(true)

const emit = defineEmits(['update:modelValue'])

const activeCount = computed(() => {
  return props.filters.reduce((acc, f) => {
    const v = props.modelValue[f.key]
    const def = props.defaults[f.key]
    if (v === null || v === undefined || v === '' || v === def) return acc
    return acc + 1
  }, 0)
})

function updateField(key, value) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

function reset() {
  emit('update:modelValue', { ...props.defaults })
}
</script>
<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>