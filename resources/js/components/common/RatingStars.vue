<template>
  <div class="rating-stars" :class="{ readonly }">
    <div class="d-flex align-center ga-1">
      <v-icon v-for="n in 5" :key="n" :size="size" :color="starColor(n)" :class="{ interactive: !readonly }"
        @click="!readonly && onStarClick(n)" @mouseenter="!readonly && (hoverValue = n)"
        @mouseleave="!readonly && (hoverValue = null)">
        {{ starIcon(n) }}
      </v-icon>
      <span v-if="showLabel" class="ml-2 text-body-2">
        <template v-if="readonly">
          <strong>{{ formattedAverage }}</strong>
          <span class="text-medium-emphasis"> ({{ count }} {{ count === 1 ? 'vote' : 'votes' }})</span>
        </template>
        <template v-else-if="myRating">
          Votre note : <strong>{{ myRating }}/5</strong>
          <v-btn icon="mdi-close" size="x-small" variant="text" class="ml-1" @click="onClear" />
        </template>
        <template v-else>
          <span class="text-medium-emphasis">Notez ce projet</span>
        </template>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  average: { type: Number, default: 0 },
  count: { type: Number, default: 0 },
  myRating: { type: Number, default: null },
  readonly: { type: Boolean, default: false },
  size: { type: [String, Number], default: 'small' },
  showLabel: { type: Boolean, default: true },
})

const emit = defineEmits(['rate', 'clear'])

const hoverValue = ref(null)

const displayedValue = computed(() => {
  if (!props.readonly && hoverValue.value !== null) return hoverValue.value
  if (props.readonly) return props.average
  return props.myRating ?? 0
})

const formattedAverage = computed(() => {
  if (!props.count) return '—'
  return props.average.toFixed(1)
})

function starIcon(n) {
  const v = displayedValue.value
  if (v >= n) return 'mdi-star'
  if (v >= n - 0.5) return 'mdi-star-half-full'
  return 'mdi-star-outline'
}

function starColor(n) {
  return displayedValue.value >= n - 0.5 ? 'amber' : 'grey-lighten-1'
}

function onStarClick(n) {
  emit('rate', n)
}

function onClear() {
  emit('clear')
}
</script>

<style scoped>
.interactive {
  cursor: pointer;
  transition: transform 0.1s;
}

.interactive:hover {
  transform: scale(1.15);
}
</style>