<template>
  <v-container>

    <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/games" class="mb-4">
      Retour à la bibliothèque
    </v-btn>

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else-if="template">

      <v-row>

        <!-- Détails du jeu -->
        <v-col cols="12" md="7">
          <v-card class="pa-6">

            <div class="d-flex align-center justify-space-between mb-4">
              <h1 class="text-h4">{{ template.name }}</h1>
              <v-chip v-if="template.type" color="primary" variant="tonal">
                {{ template.type }}
              </v-chip>
            </div>

            <p class="text-body-1 mb-4">{{ template.description }}</p>

            <div class="d-flex flex-wrap ga-2 mb-6">
              <v-chip prepend-icon="mdi-account-group">
                {{ template.min_players }}-{{ template.max_players }} joueurs
              </v-chip>
              <v-chip prepend-icon="mdi-clock-outline">
                {{ template.duration_min }}-{{ template.duration_max }} min
              </v-chip>
            </div>
            <div class="mb-4">
              <p class="text-body-2 mb-1">Note</p>
              <RatingStars :average="template.average_rating ?? 0" :count="template.ratings_count ?? 0"
                :my-rating="template.my_rating" :readonly="false" size="default" @rate="rateTemplate"
                @clear="clearRating" />
            </div>

            <v-divider class="mb-4" />

            <h2 class="text-h6 mb-3">Règles du jeu</h2>
            <div class="text-body-2" style="white-space: pre-line">{{ template.rules }}</div>

            <v-divider class="my-4" />

            <h2 class="text-h6 mb-3">Cartes incluses</h2>
            <v-row density="comfortable">
              <v-col v-for="object in template.objects" :key="object.id" cols="12" sm="6">
                <v-card variant="tonal" :color="object.default_color">
                  <v-card-text>
                    <div class="d-flex justify-space-between align-center mb-1">
                      <strong>{{ object.name }}</strong>
                      <v-chip size="x-small">×{{ object.quantity }}</v-chip>
                    </div>
                    <p class="text-caption mb-0">{{ object.description }}</p>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card>
          <CommentsAccordion :template-id="template.id" :current-user-id="currentUserId" @error="showError"
            @success="showSuccess" />
        </v-col>

        <!-- Configuration -->
        <v-col cols="12" md="5">
          <v-card class="pa-6 sticky-top">

            <h2 class="text-h6 mb-4">Configurer votre partie</h2>

            <!-- Mode de support -->
            <div class="text-caption text-medium-emphasis mb-2">MODE DE JEU</div>

            <v-card class="pa-3 mb-2 cursor-pointer" :variant="config.mode === 'printable' ? 'tonal' : 'outlined'"
              :color="config.mode === 'printable' ? 'primary' : ''" @click="config.mode = 'printable'">
              <div class="d-flex align-center">
                <v-icon size="32" class="me-3">mdi-printer</v-icon>
                <div>
                  <div class="font-weight-bold">Imprimer les cartes</div>
                  <div class="text-caption">Cartes personnalisables avec vos photos</div>
                </div>
              </div>
            </v-card>

            <v-card v-if="supportsCartesClassiques" class="pa-3 mb-4 cursor-pointer"
              :variant="config.mode === 'existing_deck' ? 'tonal' : 'outlined'"
              :color="config.mode === 'existing_deck' ? 'primary' : ''" @click="config.mode = 'existing_deck'">
              <div class="d-flex align-center">
                <v-icon size="32" class="me-3">mdi-cards</v-icon>
                <div>
                  <div class="font-weight-bold">Jeu de cartes classique</div>
                  <div class="text-caption">Génère un pense-bête (Roi = Loup, etc.)</div>
                </div>
              </div>
            </v-card>

            <!-- Nom de la partie -->
            <v-text-field v-model="config.title" label="Nom de votre partie" variant="outlined"
              :error-messages="errors.name" class="mb-3" />

            <v-btn block color="primary" size="large" :loading="generating" :disabled="!canGenerate"
              prepend-icon="mdi-cards-playing" @click="generateGame">
              Générer mon jeu
            </v-btn>
            <v-divider class="my-4" />
            <v-btn block variant="text" color="error" size="small" prepend-icon="mdi-flag-outline"
              @click="reportOpen = true">
              Signaler ce modèle
            </v-btn>

          </v-card>
        </v-col>

      </v-row>

    </div>
    <v-dialog v-model="similarDialog" max-width="500">
      <v-card class="pa-4">
        <v-card-title>Projets similaires existants</v-card-title>

        <v-card-text>
          <p class="mb-3">
            Tu as déjà {{ similarProjects.length }} projet(s) avec ce jeu et ce mode.
            Veux-tu reprendre l'un d'eux ou en créer un nouveau ?
          </p>

          <v-list>
            <v-list-item v-for="p in similarProjects" :key="p.id" :title="p.name" :subtitle="formatDate(p.created_at)"
              prepend-icon="mdi-cards-playing" @click="goToProject(p.id)" />
          </v-list>
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="similarDialog = false">Annuler</v-btn>
          <v-btn color="primary" :loading="generating" @click="createProject">
            Créer un nouveau projet
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <ReportModal v-if="template" v-model="reportOpen" reportable-type="template" :reportable-id="template.id"
      :target-label="template.name"
      @reported="showSuccess('Signalement envoyé. Merci, un administrateur examinera votre demande.')" />

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" location="bottom right">
      {{ snackbar.message }}
    </v-snackbar>

  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getUser } from '../../router'
import { api } from '../../api'
import RatingStars from '../../components/common/RatingStars.vue'
import CommentsAccordion from '../../components/common/CommentsAccordion.vue'
import ReportModal from '../../components/common/ReportModal.vue'

const router = useRouter()
const route = useRoute()

const currentUserId = ref(null)

const template = ref(null)
const loading = ref(true)
const generating = ref(false)
const errors = ref({})
const snackbar = ref({ show: false, message: '', color: 'success' })

const similarProjects = ref([])
const similarDialog = ref(false)

const reportOpen = ref(false)

const config = ref({
  title: '',
  mode: 'printable',
  players: 8,
})

const canGenerate = computed(() => {
  return config.value.title.trim() && config.value.mode && config.value.players > 0
})
const supportsCartesClassiques = computed(() =>
  template.value?.formats?.some(f => f.slug === 'cartes-classiques') ?? false
)

function showError(msg) {
  snackbar.value = { show: true, message: msg, color: 'error' }
}

function showSuccess(msg) {
  snackbar.value = { show: true, message: msg, color: 'success' }
}

async function loadTemplate() {
  try {
    const { data } = await api.get(`/templates/${route.params.slug}`)
    template.value = data
    config.value.players = data.min_players
    config.value.title = data.name
  } catch {
    router.push('/games')
  } finally {
    loading.value = false
  }
}

async function generateGame() {
  errors.value = {}

  if (!config.value.title.trim()) {
    errors.value.name = ['Le nom est requis.']
    return
  }

  // ✅ Vérifie d'abord les projets similaires
  try {
    const { data } = await api.post('/projects/find-similar', {
      template_id: template.value.id,
      mode: config.value.mode,
    })

    if (data.length > 0) {
      similarProjects.value = data
      similarDialog.value = true
      return  // ⬅️ on attend la décision de l'user
    }
  } catch {
    // En cas d'erreur, on continue quand même
  }

  await createProject()
}

async function createProject() {
  similarDialog.value = false
  generating.value = true
  try {
    const { data } = await api.post('/projects', {
      template_id: template.value.id,
      name: config.value.title,
      mode: config.value.mode,
      players: config.value.players,
    })
    router.push(`/projects/${data.id}`)
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
    else snackbar.value = { show: true, message: 'Erreur lors de la génération.', color: 'error' }
  } finally {
    generating.value = false
  }
}
function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function goToProject(id) {
  similarDialog.value = false
  router.push(`/projects/${id}`)
}
async function rateTemplate(score) {
  try {
    await api.post('/ratings', { template_id: template.value.id, score })
    snackbar.value = { show: true, message: 'Note enregistrée.', color: 'success' }
    await loadTemplate()
  } catch {
    snackbar.value = { show: true, message: 'Erreur lors de la notation.', color: 'error' }
  }
}

function clearRating() {
  snackbar.value = { show: true, message: 'Pour modifier, cliquez sur une autre étoile.', color: 'info' }
}

onMounted(async () => {
  const u = await getUser()
  currentUserId.value = u?.id ?? null
  loadTemplate()
})
</script>

<style scoped>
.sticky-top {
  position: sticky;
  top: 80px;
}

.cursor-pointer {
  cursor: pointer;
}
</style>