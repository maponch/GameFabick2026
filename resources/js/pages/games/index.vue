<template>
  <v-container>

    <h1 class="text-h4 mb-2">Bibliothèque de jeux 🎲</h1>
    <p class="text-body-2 text-medium-emphasis mb-6">
      Choisissez un jeu à personnaliser et générer
    </p>

    <!-- Filtres -->
    <v-row class="mb-4">
      <v-col cols="12" sm="6" md="4">
        <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" placeholder="Rechercher..." variant="outlined"
          density="compact" hide-details clearable />
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <v-select v-model="filterPlayers" :items="playerOptions" label="Nombre de joueurs" variant="outlined"
          density="compact" hide-details clearable />
      </v-col>
    </v-row>

    <!-- Chargement -->
    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <!-- Aucun résultat -->
    <div v-else-if="filteredTemplates.length === 0" class="text-center pa-10 text-medium-emphasis">
      <v-icon size="64" class="mb-3">mdi-cards-outline</v-icon>
      <p>Aucun jeu ne correspond à votre recherche.</p>
    </div>

    <!-- Grille de jeux -->
    <v-row v-else>
      <v-col v-for="template in filteredTemplates" :key="template.id" cols="12" sm="6" md="4">
        <v-card class="h-100 d-flex flex-column cursor-pointer" hover @click="goToTemplate(template.slug)">
          <v-card-title class="d-flex align-center justify-space-between">
            <span>{{ template.name }}</span>
            <v-chip v-if="template.type" size="x-small" color="primary" variant="tonal">
              {{ template.type }}
            </v-chip>
          </v-card-title>

          <v-card-text class="flex-grow-1">
            <p class="text-body-2 mb-3"
              style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
              {{ template.description }}
            </p>

            <div class="d-flex flex-wrap ga-2">
              <v-chip size="x-small" prepend-icon="mdi-account-group">
                {{ template.min_players }}-{{ template.max_players }} joueurs
              </v-chip>
              <v-chip size="x-small" prepend-icon="mdi-clock-outline">
                {{ template.duration_min }}-{{ template.duration_max }} min
              </v-chip>
              <v-chip v-if="template.supports_existing_deck" size="x-small" color="success" variant="tonal"
                prepend-icon="mdi-cards">
                Jeu de cartes
              </v-chip>
            </div>
          </v-card-text>

          <v-card-actions>
            <v-btn block color="primary" variant="tonal" append-icon="mdi-arrow-right">
              Configurer
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'

const router = useRouter()

const templates = ref([])
const loading = ref(true)
const search = ref('')
const filterPlayers = ref(null)

const playerOptions = [
  { title: '2-4 joueurs', value: '2-4' },
  { title: '5-8 joueurs', value: '5-8' },
  { title: '9-12 joueurs', value: '9-12' },
  { title: '13+ joueurs', value: '13+' },
]

const filteredTemplates = computed(() => {
  let result = templates.value

  if (search.value) {
    const q = search.value.toLowerCase()
    result = result.filter(t =>
      t.name.toLowerCase().includes(q) ||
      t.description.toLowerCase().includes(q)
    )
  }

  if (filterPlayers.value) {
    result = result.filter(t => {
      const [min, max] = filterPlayers.value === '13+'
        ? [13, 999]
        : filterPlayers.value.split('-').map(Number)
      return t.max_players >= min && t.min_players <= max
    })
  }

  return result
})

function goToTemplate(slug) {
  router.push(`/games/${slug}`)
}

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