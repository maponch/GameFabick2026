<template>
  <div class="quiz-card">
    <div class="quiz-header" :style="{ backgroundColor: card.color }">
      <div class="quiz-category">{{ card.title }}</div>
      <div v-if="card.customData?.difficulte" class="quiz-difficulty" :class="difficultyClass">
        {{ card.customData.difficulte }}
      </div>
    </div>

    <div class="quiz-question">
      <div class="quiz-question-label">Question</div>
      <p>{{ card.customData?.question || card.description }}</p>
    </div>

    <div class="quiz-answer">
      <div class="quiz-answer-label">Réponse</div>
      <p>{{ card.customData?.reponse || '—' }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  card: { type: Object, required: true },
  schema: { type: Array, default: () => [] },
})

const difficultyClass = computed(() => {
  const d = props.card.customData?.difficulte
  if (d === 'Facile') return 'diff-easy'
  if (d === 'Moyen') return 'diff-medium'
  if (d === 'Difficile') return 'diff-hard'
  return ''
})
</script>

<style scoped>
.quiz-card {
  height: 80mm;
  border-radius: 3mm;
  overflow: hidden;
  background: white;
  color: #222;
  display: flex;
  flex-direction: column;
  border: 1.5px solid #333;
  box-sizing: border-box;
}

.quiz-header {
  padding: 3mm 4mm;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 3mm;
  color: white;
  border-bottom: 1.5px solid #333;
  min-height: 12mm;
}

.quiz-category {
  font-size: 12pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.quiz-difficulty {
  font-size: 8pt;
  font-weight: 700;
  padding: 2mm;
  border-radius: 2mm;
  background: white;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  flex-shrink: 0;
}

.diff-easy {
  color: #2e7d32;
}

.diff-medium {
  color: #f57c00;
}

.diff-hard {
  color: #c62828;
}

.quiz-question {
  flex: 1;
  padding: 4mm;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.quiz-question-label {
  font-size: 7pt;
  font-weight: 700;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 2mm;
}

.quiz-question p {
  font-size: 12pt;
  line-height: 1.35;
  margin: 0;
  font-weight: 500;
}

.quiz-answer {
  padding: 3mm 4mm;
  background: #f5f5f5;
  border-top: 1px dashed #999;
}

.quiz-answer-label {
  font-size: 7pt;
  font-weight: 700;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 1mm;
}

.quiz-answer p {
  font-size: 10pt;
  line-height: 1.3;
  margin: 0;
  font-weight: 600;
}
</style>