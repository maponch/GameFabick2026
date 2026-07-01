<template>
  <div class="speed-card" :style="{ backgroundColor: card.color }">
    <div class="speed-label">{{ card.title }}</div>
    <div class="speed-symbol">{{ card.customData?.symbole || '?' }}</div>
  </div>
</template>

<script setup>
defineProps({
  card: { type: Object, required: true },
  schema: { type: Array, default: () => [] },
})
</script>

<style scoped>
.speed-card {
  height: 80mm;
  border-radius: 4mm;
  padding: 6mm;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: relative;
  border: 3px solid rgba(255, 255, 255, 0.8);
  box-sizing: border-box;
}

.speed-label {
  font-size: 11pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  opacity: 0.85;
  margin-bottom: 4mm;
}

.speed-symbol {
  font-size: 72pt;
  line-height: 1;
  text-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}
</style>