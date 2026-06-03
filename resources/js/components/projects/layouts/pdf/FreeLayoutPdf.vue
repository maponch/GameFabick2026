<template>
  <div class="card" :style="{ backgroundColor: card.color }">
    <div class="card-title">{{ card.title }}</div>
    <div v-if="card.description" class="card-description">{{ card.description }}</div>

    <div v-if="customFields.length > 0" class="card-custom-fields">
      <div v-for="field in customFields" :key="field.key" class="custom-field">
        <span class="custom-field-label">{{ field.label }} :</span>
        <span class="custom-field-value">{{ formatValue(field, card.customData?.[field.key]) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  card: { type: Object, required: true },
  schema: { type: Array, default: () => [] },
})

const customFields = computed(() => {
  return (props.schema ?? []).filter(field => {
    const val = props.card.customData?.[field.key]
    return val !== null && val !== undefined && val !== ''
  })
})

function formatValue(field, value) {
  if (field.type === 'boolean') return value ? 'Oui' : 'Non'
  return value
}
</script>

<style scoped>
.card {
  height: 80mm;
  border-radius: 4mm;
  padding: 5mm;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  color: white;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-title {
  font-size: 14pt;
  font-weight: bold;
  margin-bottom: 3mm;
}

.card-description {
  font-size: 9pt;
  line-height: 1.3;
  margin-bottom: 3mm;
}

.card-custom-fields {
  font-size: 8pt;
  margin-top: 2mm;
  text-align: left;
}

.custom-field {
  margin-bottom: 1mm;
}

.custom-field-label {
  font-weight: 600;
}

.custom-field-value {
  margin-left: 2mm;
}
</style>