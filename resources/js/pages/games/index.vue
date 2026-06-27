<template>
  <v-container>
    <h1 class="text-h4 mb-2">Bibliothèque de jeux 🎲</h1>
    <p class="text-body-2 text-medium-emphasis mb-6">
      Choisissez un jeu à personnaliser et générer
    </p>

    <FilterToolbar v-model="filterState" :filters="filterConfig" :defaults="defaults" />

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else-if="filteredTemplates.length === 0" class="text-center pa-10 text-medium-emphasis">
      <v-icon size="64" class="mb-3">mdi-cards-outline</v-icon>
      <p>Aucun jeu ne correspond à votre recherche.</p>
    </div>

    <v-row v-else>
      <v-col v-for="template in filteredTemplates" :key="template.id" cols="12" sm="6" md="4">
        <ProjectCard :title="template.name" :description="template.description" :type-label="template.type"
          :chips="buildChips(template)" :rating="{ average: template.average_rating, count: template.ratings_count }"
          button-label="Configurer" @click="goToTemplate(template.slug)" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'
import ProjectCard from '../../components/games/ProjectCard.vue'
import FilterToolbar from '../../components/common/FilterToolbar.vue'
import { useFiltersInUrl } from '../../composables/useFiltersInUrl'

const router = useRouter()
const templates = ref([])
const loading = ref(true)

const defaults = {
  search: '',
  type: null,
  format: null,
  players: null,
  duration: null,
  rating_min: null,
}
const filterState = useFiltersInUrl(defaults)

const typeOptions = computed(() => {
  const types = [...new Set(templates.value.map(t => t.type).filter(Boolean))]
  return types.sort().map(t => ({ title: t, value: t }))
})

const formatOptions = computed(() => {
  const m = new Map()
  for (const t of templates.value) {
    for (const f of (t.formats ?? [])) m.set(f.slug, f.name)
  }
  return [...m.entries()].map(([slug, name]) => ({ title: name, value: slug }))
})

const filterConfig = computed(() => [
  { key: 'search', type: 'text', label: 'Rechercher' },
  { key: 'type', type: 'select', label: 'Type', options: typeOptions.value },
  { key: 'format', type: 'select', label: 'Format', options: formatOptions.value },
  {
    key: 'players', type: 'select', label: 'Joueurs', options: [
      { title: '2-4 joueurs', value: '2-4' },
      { title: '5-8 joueurs', value: '5-8' },
      { title: '9-12 joueurs', value: '9-12' },
      { title: '13+ joueurs', value: '13+' },
    ]
  },
  {
    key: 'duration', type: 'select', label: 'Durée', options: [
      { title: 'Moins de 30 min', value: 'short' },
      { title: '30-60 min', value: 'medium' },
      { title: 'Plus de 60 min', value: 'long' },
    ]
  },
  {
    key: 'rating_min', type: 'select', label: 'Note minimum', options: [
      { title: '★ 2+', value: 2 },
      { title: '★ 3+', value: 3 },
      { title: '★ 4+', value: 4 },
      { title: '★ 5', value: 5 },
    ]
  },
])

function matchesPlayers(t, bucket) {
  const ranges = { '2-4': [2, 4], '5-8': [5, 8], '9-12': [9, 12], '13+': [13, 999] }
  const [min, max] = ranges[bucket] ?? [0, 999]
  return t.max_players >= min && t.min_players <= max
}

function matchesDuration(t, bucket) {
  const avg = ((t.duration_min ?? 0) + (t.duration_max ?? 0)) / 2
  if (bucket === 'short') return avg < 30
  if (bucket === 'medium') return avg >= 30 && avg <= 60
  if (bucket === 'long') return avg > 60
  return true
}

const filteredTemplates = computed(() => {
  const f = filterState.value
  let result = templates.value

  if (f.search?.trim()) {
    const q = f.search.toLowerCase().trim()
    result = result.filter(t =>
      t.name?.toLowerCase().includes(q) ||
      t.description?.toLowerCase().includes(q)
    )
  }
  if (f.type) result = result.filter(t => t.type === f.type)
  if (f.format) result = result.filter(t => t.formats?.some(x => x.slug === f.format))
  if (f.players) result = result.filter(t => matchesPlayers(t, f.players))
  if (f.duration) result = result.filter(t => matchesDuration(t, f.duration))
  if (f.rating_min) result = result.filter(t => (t.average_rating ?? 0) >= f.rating_min)

  return result
})

function buildChips(template) {
  const chips = [
    { label: `${template.min_players}-${template.max_players} joueurs`, icon: 'mdi-account-group' },
    { label: `${template.duration_min}-${template.duration_max} min`, icon: 'mdi-clock-outline' },
  ]
  if (template.formats?.some(f => f.slug === 'cartes-classiques')) {
    chips.push({ label: 'Jeu de cartes', color: 'success', icon: 'mdi-cards' })
  }
  return chips
}

function goToTemplate(slug) { router.push(`/games/${slug}`) }

onMounted(async () => {
  try {
    const { data } = await api.get('/templates')
    templates.value = data
  } catch (e) {
    console.error('Erreur chargement templates:', e)
  } finally {
    loading.value = false
  }
})
</script>