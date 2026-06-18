<template>
  <v-container>
    <h1 class="text-h4 mb-2">Galerie communautaire 🎨</h1>
    <p class="text-body-2 text-medium-emphasis mb-6">
      Découvrez les créations publiées par les autres utilisateurs.
    </p>

    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="3">
        <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" placeholder="Rechercher..." variant="outlined"
          density="compact" hide-details clearable />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-select v-model="filterType" :items="typeOptions" label="Type" variant="outlined" density="compact"
          hide-details clearable />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-select v-model="filterFormat" :items="formatOptions" label="Format" variant="outlined" density="compact"
          hide-details clearable />
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-select v-model="filterDuplicable" :items="duplicableOptions" label="Duplication" variant="outlined"
          density="compact" hide-details clearable />
      </v-col>
    </v-row>

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
          button-label="Voir le projet" @click="goToProject(project.id)" />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'
import ProjectCard from '../../components/games/ProjectCard.vue'

const router = useRouter()

const projects = ref([])
const loading = ref(true)
const search = ref('')
const filterType = ref(null)
const filterFormat = ref(null)
const filterDuplicable = ref(null)

const duplicableOptions = [
  { title: 'Dupliquable', value: 'yes' },
  { title: 'Non dupliquable', value: 'no' },
]

const typeOptions = computed(() => {
  const types = [...new Set(projects.value.map(p => p.type).filter(Boolean))]
  return types.sort().map(t => ({ title: t, value: t }))
})

const formatOptions = computed(() => {
  const formats = new Map()
  for (const p of projects.value) {
    for (const f of (p.formats ?? [])) {
      formats.set(f.slug, f.name)
    }
  }
  return [...formats.entries()].map(([slug, name]) => ({ title: name, value: slug }))
})

const filteredProjects = computed(() => {
  let result = projects.value

  if (search.value?.trim()) {
    const q = search.value.toLowerCase().trim()
    result = result.filter(p =>
      p.name?.toLowerCase().includes(q) ||
      p.description?.toLowerCase().includes(q) ||
      p.author?.username?.toLowerCase().includes(q)
    )
  }

  if (filterType.value) {
    result = result.filter(p => p.type === filterType.value)
  }

  if (filterFormat.value) {
    result = result.filter(p => p.formats?.some(f => f.slug === filterFormat.value))
  }

  if (filterDuplicable.value === 'yes') {
    result = result.filter(p => p.allow_duplication)
  } else if (filterDuplicable.value === 'no') {
    result = result.filter(p => !p.allow_duplication)
  }

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