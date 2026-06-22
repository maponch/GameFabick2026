<template>
  <v-card class="h-100 d-flex flex-column cursor-pointer" hover @click="$emit('click')">
    <v-card-title class="d-flex align-center justify-space-between">
      <span class="text-truncate">{{ title }}</span>
      <v-chip v-if="typeLabel" size="x-small" color="primary" variant="tonal">
        {{ typeLabel }}
      </v-chip>
    </v-card-title>

    <v-card-subtitle v-if="subtitle">
      {{ subtitle }}
    </v-card-subtitle>

    <v-card-text class="flex-grow-1">
      <div v-if="rating" class="mb-2">
        <RatingStars :average="rating.average ?? 0" :count="rating.count ?? 0" :readonly="true" size="x-small" />
      </div>

      <p v-if="description" class="text-body-2 mb-3 description-clamp">
        {{ description }}
      </p>

      <div v-if="chips.length > 0" class="d-flex flex-wrap ga-2">
        <v-chip v-for="(chip, i) in chips" :key="i" size="x-small" :color="chip.color"
          :variant="chip.variant ?? 'tonal'" :prepend-icon="chip.icon">
          {{ chip.label }}
        </v-chip>
      </div>
    </v-card-text>

    <v-card-actions>
      <v-btn block color="primary" variant="tonal" :append-icon="buttonIcon">
        {{ buttonLabel }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import RatingStars from '../common/RatingStars.vue'

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: null },
  description: { type: String, default: null },
  typeLabel: { type: String, default: null },
  chips: { type: Array, default: () => [] },
  buttonLabel: { type: String, default: 'Voir' },
  buttonIcon: { type: String, default: 'mdi-arrow-right' },
  rating: { type: Object, default: null },
})

defineEmits(['click'])
</script>

<style scoped>
.description-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>