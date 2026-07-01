<template>
  <v-container>
    <h1 class="text-h4 mb-2">Galerie communautaire 🎨</h1>
    <p class="text-body-2 text-medium-emphasis mb-6">
      Découvrez les créations publiées par les autres utilisateurs.
    </p>

    <FilterToolbar v-model="filterState" :filters="filterConfig" :defaults="defaults" />

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else-if="filteredProjects.length === 0" class="text-center pa-10 text-medium-emphasis">
      <v-icon size="64" class="mb-3">mdi-account-group-outline</v-icon>
      <p>Aucun projet ne correspond à votre recherche.</p>
    </div>

    <v-row v-else>
      <v-col v-for="project in filteredProjects" :key="project.id" cols="12" sm="6" md="4">
        <ProjectCard :title="project.name"
          :subtitle="project.author?.username ? `par ${project.author.username}` : null"
          :description="project.description" :type-label="project.type" :chips="buildChips(project)"
          :rating="{ average: project.average_rating, count: project.ratings_count }" button-label="Voir le projet"
          @click="goToProject(project.id)" />
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
const projects = ref([])
const loading = ref(true)

const defaults = {
  search: '',
  type: null,
  format: null,
  duplicable: null,
  complete: null,
  rating_min: null,
  players: null,
  duration: null,
}
const filterState = useFiltersInUrl(defaults)

const typeOptions = computed(() => {
  const types = [...new Set(projects.value.map(p => p.type).filter(Boolean))]
  return types.sort().map(t => ({ title: t, value: t }))
})

const formatOptions = computed(() => {
  const m = new Map()
  for (const p of projects.value) {
    for (const f of (p.formats ?? [])) m.set(f.slug, f.name)
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
  { key: 'duplicable', type: 'toggle', label: 'Dupliquable' },
  { key: 'complete', type: 'toggle', label: 'Complet' },
  {
    key: 'rating_min', type: 'select', label: 'Note minimum', options: [
      { title: '★ 2+', value: 2 },
      { title: '★ 3+', value: 3 },
      { title: '★ 4+', value: 4 },
      { title: '★ 5', value: 5 },
    ]
  },
])

function matchesPlayers(p, bucket) {
  const ranges = {
    '2-4': [2, 4],
    '5-8': [5, 8],
    '9-12': [9, 12],
    '13+': [13, 999],
  }
  const [min, max] = ranges[bucket] ?? [0, 999]
  return p.max_players >= min && p.min_players <= max
}

function matchesDuration(p, bucket) {
  const avg = ((p.duration_min ?? 0) + (p.duration_max ?? 0)) / 2
  if (bucket === 'short') return avg < 30
  if (bucket === 'medium') return avg >= 30 && avg <= 60
  if (bucket === 'long') return avg > 60
  return true
}

const filteredProjects = computed(() => {
  const f = filterState.value
  let result = projects.value

  if (f.search?.trim()) {
    const q = f.search.toLowerCase().trim()
    result = result.filter(p =>
      p.name?.toLowerCase().includes(q) ||
      p.description?.toLowerCase().includes(q) ||
      p.author?.username?.toLowerCase().includes(q)
    )
  }
  if (f.type) result = result.filter(p => p.type === f.type)
  if (f.format) result = result.filter(p => p.formats?.some(x => x.slug === f.format))
  if (f.players) result = result.filter(p => matchesPlayers(p, f.players))
  if (f.duration) result = result.filter(p => matchesDuration(p, f.duration))
  if (f.duplicable === true) result = result.filter(p => p.allow_duplication)
  if (f.complete === true) result = result.filter(p => p.publishable?.ready === true)
  if (f.rating_min) result = result.filter(p => (p.average_rating ?? 0) >= f.rating_min)

  return result
})

function buildChips(project) {
  const chips = [
    { label: `${project.min_players}-${project.max_players} joueurs`, icon: 'mdi-account-group' },
    { label: `${project.duration_min}-${project.duration_max} min`, icon: 'mdi-clock-outline' },
    { label: `${project.objects_count} cartes`, icon: 'mdi-cards' },
  ]
  if (project.allow_duplication) {
    chips.push({ label: 'Dupliquable', color: 'success', icon: 'mdi-content-copy' })
  }
  if (project.publishable?.ready) {
    chips.push({ label: 'Complet', color: 'success', icon: 'mdi-check-circle' })
  }
  return chips
}

function goToProject(id) {
  router.push(`/community/${id}`)
}

onMounted(async () => {
  try {
    const { data } = await api.get('/community')
    projects.value = data
  } catch (e) {
    console.error('Erreur chargement community:', e)
  } finally {
    loading.value = false
  }
})
</script>