<template>
  <div class="card" :style="{ backgroundColor: card.color }">
    <template v-if="isEmpty">
      <div class="card-body card-body--centered">
        <div class="card-title-centered">{{ card.title }}</div>
      </div>
    </template>

    <template v-else>
      <div class="card-header">
        <div class="card-title">{{ card.title }}</div>
      </div>

      <div class="card-body">
        <div v-if="card.description" class="card-description">
          {{ card.description }}
        </div>

        <div v-if="customFields.length > 0" class="card-fields-wrapper">
          <div class="card-fields-separator" />
          <div class="card-fields">
            <div v-for="field in customFields" :key="field.key" class="card-field">
              <span class="card-field-label">{{ field.label }}</span>
              <span class="card-field-value">{{ formatValue(field, card.customData?.[field.key]) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  card: { type: Object, required: true },
  schema: { type: Array, default: () => [] },
})
const isEmpty = computed(() => {
  return !props.card.description && customFields.value.length === 0
})

const customFields = computed(() => {
  return (props.schema ?? []).filter(field => {
    const val = props.card.customData?.[field.key]
    if (field.type === 'boolean') {
      if (field.hide_if_false && !val) return false
      return val !== null && val !== undefined
    }
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
  border-radius: 3mm;
  padding: 3mm;
  display: flex;
  flex-direction: column;
  color: white;
  box-sizing: border-box;
}

.card-header {
  height: 16mm;
  margin-top: -3mm;
  margin-bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 2mm;
}

.card-title {
  font-size: 14pt;
  font-weight: bold;
  text-align: center;
  line-height: 1.15;
  word-break: break-word;
}

.card-body {
  flex: 1;
  background: white;
  color: #000;
  border-radius: 2.5mm;
  padding: 4mm;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}
.card-body--centered {
  align-items: center;
  justify-content: center;
  padding: 6mm;
}

.card-title-centered {
  color: #000;
  font-size: 16pt;
  font-weight: bold;
  text-align: center;
  line-height: 1.2;
  word-break: break-word;
}

.card-description {
  font-size: 9pt;
  line-height: 1.3;
}

.card-fields-wrapper {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 3mm;
}

.card-fields-separator {
  height: 0.2mm;
  background: #ddd;
}

.card-fields {
  font-size: 9pt;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 1mm 3mm;
  align-content: end;
}

.card-field {
  display: contents;
}

.card-field-label {
  font-weight: 600;
  color: #555;
}

.card-field-value {
  text-align: right;
  word-break: break-word;
}
</style>