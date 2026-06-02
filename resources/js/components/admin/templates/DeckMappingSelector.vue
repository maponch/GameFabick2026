<template>
  <div>
    <div class="d-flex flex-wrap ga-2 mb-3">
      <v-chip v-for="rank in ranks" :key="`rank-${rank.id}`" size="small"
        :color="isRankSelected(rank.id) ? 'primary' : undefined"
        :variant="isRankSelected(rank.id) ? 'flat' : 'outlined'" @click="toggleRank(rank.id)">
        Tous les {{ rank.label }}
      </v-chip>
    </div>

    <div class="d-flex flex-wrap ga-2 mb-4">
      <v-chip v-for="suit in suits" :key="`suit-${suit.id}`" size="small"
        :color="isSuitSelected(suit.id) ? 'primary' : undefined"
        :variant="isSuitSelected(suit.id) ? 'flat' : 'outlined'" @click="toggleSuit(suit.id)">
        {{ suit.symbol }} {{ suit.label }}
      </v-chip>
    </div>

    <v-divider class="mb-3" />

    <div class="deck-grid mb-3">
      <v-tooltip v-for="card in gridCards" :key="card.id"
        :text="isLocked(card.id) ? `Pris par : ${lockedBy(card.id)}` : ''" :disabled="!isLocked(card.id)"
        location="top">
        <template #activator="{ props: tProps }">
          <button v-bind="tProps" type="button" class="deck-card" :class="{
            selected: isSelected(card.id),
            red: card.color === 'red',
            locked: isLocked(card.id),
          }" :disabled="isLocked(card.id)" @click="toggleCard(card.id)">
            {{ card.short }}
          </button>
        </template>
      </v-tooltip>
    </div>

    <div class="d-flex flex-wrap ga-2 mb-3">
      <v-tooltip v-for="joker in jokers" :key="joker.id"
        :text="isLocked(joker.id) ? `Pris par : ${lockedBy(joker.id)}` : ''" :disabled="!isLocked(joker.id)"
        location="top">
        <template #activator="{ props: tProps }">
          <v-chip v-bind="tProps" size="small" :color="isSelected(joker.id) ? 'primary' : undefined"
            :variant="isSelected(joker.id) ? 'flat' : 'outlined'" :class="{ 'joker-locked': isLocked(joker.id) }"
            @click="toggleCard(joker.id)">
            {{ joker.label }}
          </v-chip>
        </template>
      </v-tooltip>
    </div>

    <div class="d-flex align-center justify-space-between">
      <span class="text-caption text-medium-emphasis">
        {{ selected.length }} carte(s) sélectionnée(s)
      </span>
      <v-btn size="small" variant="text" :disabled="selected.length === 0" @click="clearAll">
        Tout désélectionner
      </v-btn>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  RANKS, SUITS, JOKERS,
  cardId, cardsByRank, cardsBySuit,
  isRankFullySelected, isSuitFullySelected,
} from '../../../constants/classicDeck'

const model = defineModel({ type: Array, default: () => [] })

const ranks = RANKS
const suits = SUITS
const jokers = JOKERS

const selected = computed(() => model.value ?? [])

// Grille : 52 cartes ordonnées par enseigne puis valeur
const gridCards = computed(() => {
  const cards = []
  for (const s of SUITS) {
    for (const r of RANKS) {
      cards.push({
        id: cardId(r.id, s.id),
        short: `${r.label}${s.symbol}`,
        color: s.color,
      })
    }
  }
  return cards
})
const props = defineProps({
  locked: { type: Object, default: () => ({}) },
})

function isSelected(id) {
  return selected.value.includes(id)
}

function isRankSelected(rankId) {
  return isRankFullySelected(rankId, selected.value)
}

function isSuitSelected(suitId) {
  return isSuitFullySelected(suitId, selected.value)
}

function setSelection(next) {
  model.value = Array.from(new Set(next))
}

function toggleCard(id) {
  if (isLocked(id)) return
  if (isSelected(id)) {
    setSelection(selected.value.filter(c => c !== id))
  } else {
    setSelection([...selected.value, id])
  }
}

function toggleRank(rankId) {
  const group = cardsByRank(rankId).filter(id => !isLocked(id))
  if (group.length === 0) return
  const allSelected = group.every(id => selected.value.includes(id))
  if (allSelected) {
    setSelection(selected.value.filter(c => !group.includes(c)))
  } else {
    setSelection([...selected.value, ...group])
  }
}

function toggleSuit(suitId) {
  const group = cardsBySuit(suitId).filter(id => !isLocked(id))
  if (group.length === 0) return
  const allSelected = group.every(id => selected.value.includes(id))
  if (allSelected) {
    setSelection(selected.value.filter(c => !group.includes(c)))
  } else {
    setSelection([...selected.value, ...group])
  }
}

function clearAll() {
  setSelection([])
}
function isLocked(id) {
  return id in props.locked
}

function lockedBy(id) {
  return props.locked[id] ?? null
}
</script>

<style scoped>
.deck-grid {
  display: grid;
  grid-template-columns: repeat(13, minmax(0, 1fr));
  gap: 4px;
}

.deck-card {
  aspect-ratio: 2 / 3;
  border: 1px solid rgba(0, 0, 0, 0.25);
  border-radius: 4px;
  background: #fff;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #000;
  transition: all 0.12s;
  padding: 0;
  min-width: 0;
}

.deck-card.red {
  color: #d32f2f;
}

.deck-card.selected {
  background: rgb(var(--v-theme-primary));
  color: #fff;
  border-color: rgb(var(--v-theme-primary));
}

.deck-card:hover {
  transform: scale(1.05);
}
.deck-card.locked {
  opacity: 0.35;
  cursor: not-allowed;
  background: #f0f0f0;
}

.deck-card.locked:hover {
  transform: none;
}
.joker-locked {
  opacity: 0.4;
  cursor: not-allowed;
}

@media (max-width: 900px) {
  .deck-grid {
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }

  .deck-card {
    font-size: 0.6rem;
  }
}

@media (max-width: 480px) {
  .deck-grid {
    grid-template-columns: repeat(5, minmax(0, 1fr));
  }

  .deck-card {
    font-size: 0.55rem;
  }
}
</style>