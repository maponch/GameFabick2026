<template>
  <div ref="printArea" class="printable-container">
    <div v-for="(pageCards, pageIndex) in pages" :key="pageIndex" class="page">
      <div v-if="pageIndex === 0" class="printable-header">
        <h1>{{ project.name }}</h1>
        <p>{{ project.template?.name }} — {{ project.min_players }} à {{ project.max_players }} joueurs</p>
      </div>
      <div class="cards-grid">
        <component :is="layoutComponent" v-for="(card, i) in pageCards" :key="`${pageIndex}-${i}`" :card="card"
          :schema="schema" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import FreeLayoutPdf from './layouts/pdf/FreeLayoutPdf.vue'
import TitleTextLayoutPdf from './layouts/pdf/TitleTextLayoutPdf.vue'

const LAYOUT_MAP = {
  'title-text': TitleTextLayoutPdf,
}

const props = defineProps({
  project: { type: Object, required: true },
})

const printArea = ref(null)

const schema = computed(() => props.project.card_schema ?? [])
const layoutComponent = computed(() => {
  const slug = props.project.card_layout?.slug
  return LAYOUT_MAP[slug] ?? FreeLayoutPdf
})

const allCards = computed(() => {
  const cards = []
  for (const object of props.project.objects) {
    for (let i = 0; i < object.quantity; i++) {
      cards.push({
        title: object.name,
        description: object.description,
        color: object.custom_color || object.default_color || '#666',
        customData: object.custom_data ?? {},
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
  pages.push(cards.splice(0, CARDS_PER_PAGE_FIRST))
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
  max-height: 290mm;
  padding: 10mm 20mm;
  box-sizing: border-box;
  overflow: hidden;
  break-after: page;
  break-inside: avoid;
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
</style>