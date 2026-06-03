<template>
  <div class="card" :style="{ backgroundColor: card.color }">
    <div class="card-title">{{ card.title }}</div>
    <div v-if="card.description" class="card-description">{{ card.description }}</div>
  </div>
</template>

<script setup>
defineProps({
  card: { type: Object, required: true },
  schema: { type: Array, default: () => [] },
})
</script>

<style scoped>
.card {
  height: 80mm;
  border-radius: 4mm;
  padding: 8mm 5mm;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: white;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 16pt;
  font-weight: bold;
  margin-bottom: 4mm;
  line-height: 1.2;
}

.card-description {
  font-size: 10pt;
  line-height: 1.4;
}
</style>