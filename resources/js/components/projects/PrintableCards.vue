<template>
  <div ref="printArea" class="printable-container">

    <div v-for="(pageCards, pageIndex) in pages" :key="pageIndex" class="page">
      <!-- En-tête uniquement sur la première page -->
      <div v-if="pageIndex === 0" class="printable-header">
        <h1>{{ project.title }}</h1>
        <p>{{ project.template?.name }} — {{ project.min_players }} à {{ project.max_players }} joueurs</p>
      </div>

      <div class="cards-grid">
        <div v-for="(card, i) in pageCards" :key="`${pageIndex}-${i}`" class="card"
          :style="{ backgroundColor: card.color }">
          <div class="card-title">{{ card.title }}</div>
          <div class="card-description">{{ card.description }}</div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  project: { type: Object, required: true }
})

const printArea = ref(null)

const allCards = computed(() => {
  const cards = []
  for (const object of props.project.objects) {
    for (let i = 0; i < object.quantity; i++) {
      cards.push({
        title: object.custom_text || object.name,
        description: object.description,
        color: object.custom_color || object.default_color || '#666',
      })
    }
  }
  return cards
})


const CARDS_PER_PAGE_FIRST = 6 
const CARDS_PER_PAGE = 9  

const pages = computed(() => {
  const pages = []
  const cards = [...allCards.value]

  // Première page
  pages.push(cards.splice(0, CARDS_PER_PAGE_FIRST))

  // Pages suivantes
  while (cards.length > 0) {
    pages.push(cards.splice(0, CARDS_PER_PAGE))
  }

  return pages
})

defineExpose({ printArea })
</script>

<style scoped>
.printable-container {
  background: white;
  color: black;
  font-family: Arial, sans-serif;
}

.page {
  width: 210mm;
  min-height: 290mm;
  /* ← était height: 290mm */
  max-height: 290mm;
  /* ← ajouter pour éviter débordement */
  padding: 10mm 20mm;
  box-sizing: border-box;
  overflow: hidden;
  break-after: page;
  /* ← remplace page-break-after (standard moderne) */
  break-inside: avoid;
  /* ← empêche coupure interne */
}

.page:last-child {
  page-break-after: auto;
}

.printable-header {
  text-align: center;
  margin-bottom: 10mm;
  border-bottom: 2px solid #333;
  padding-bottom: 5mm;
}

.printable-header h1 {
  font-size: 24pt;
  margin: 0 0 5pt 0;
}

.printable-header p {
  font-size: 12pt;
  margin: 0;
  color: #666;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 5mm;
}

.card {
  height: 80mm;
  border-radius: 4mm;
  padding: 5mm;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: white;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 14pt;
  font-weight: bold;
  margin-bottom: 3mm;
}

.card-description {
  font-size: 9pt;
  line-height: 1.3;
}
</style>