<template>
  <v-container>
    <div v-if="loading" class="d-flex justify-center pa-8">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <template v-else-if="project">
      <div class="d-flex align-center ga-3 mb-4 flex-wrap">
        <v-btn icon="mdi-arrow-left" variant="text" @click="$router.back()" />
        <h1 class="text-h4">{{ project.name }}</h1>
        <v-chip v-if="project.type" color="primary" variant="tonal" label>{{ project.type }}</v-chip>
      </div>

      <p class="text-body-2 text-medium-emphasis mb-4">
        par <strong>{{ project.author?.username ?? 'Anonyme' }}</strong>
      </p>

      <v-row>
        <v-col cols="12" md="8">
          <v-card class="pa-4 mb-4">
            <h2 class="text-h6 mb-2">Description</h2>
            <p>{{ project.description || 'Pas de description.' }}</p>
          </v-card>

          <v-card v-if="project.rules" class="pa-4 mb-4">
            <h2 class="text-h6 mb-2">Règles</h2>
            <pre class="rules-text">{{ project.rules }}</pre>
          </v-card>

          <v-card class="pa-4 mb-4">
            <h2 class="text-h6 mb-2">Aperçu des cartes</h2>
            <v-row>
              <v-col v-for="object in project.objects" :key="object.id" cols="6" sm="4" md="3">
                <v-card v-for="n in object.quantity" :key="`${object.id}-${n}`" class="mb-3 d-flex flex-column"
                  :color="object.default_color" height="220">
                  <v-card-title class="text-center text-white pb-1">{{ object.name }}</v-card-title>
                  <v-card-text
                    class="text-center text-white flex-grow-1 d-flex flex-column align-center justify-center">
                    <p v-if="object.description" class="text-caption mb-2">{{ object.description }}</p>
                    <div v-if="objectCustomFields(object).length > 0" class="custom-fields w-100">
                      <div v-for="field in objectCustomFields(object)" :key="field.key" class="custom-field">
                        <span class="custom-field-label">{{ field.label }} :</span>
                        <span class="custom-field-value">{{ formatFieldValue(field, object.custom_data?.[field.key])
                        }}</span>
                      </div>
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card>
          <CommentsAccordion :project-id="project.id" :current-user-id="currentUserId" :is-admin="isAdmin"
            @error="showError" @success="showSuccess" />
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="pa-4 mb-4 sticky-top">
            <h2 class="text-h6 mb-3">Informations</h2>
            <div class="mb-2">
              <v-icon size="small" class="me-2">mdi-account-group</v-icon>
              {{ project.min_players }}–{{ project.max_players }} joueurs
            </div>
            <div class="mb-2">
              <v-icon size="small" class="me-2">mdi-clock-outline</v-icon>
              {{ project.duration_min }}–{{ project.duration_max }} minutes
            </div>
            <div class="mb-2">
              <v-icon size="small" class="me-2">mdi-cards</v-icon>
              {{ project.objects?.length ?? 0 }} types de cartes
            </div>
            <div v-if="project.template" class="mb-2">
              <v-icon size="small" class="me-2">mdi-content-duplicate</v-icon>
              Basé sur <strong>{{ project.template.name }}</strong>
            </div>
            <div class="mb-3">
              <p class="text-body-2 mb-1">Note</p>
              <RatingStars :average="project.average_rating ?? 0" :count="project.ratings_count ?? 0"
                :my-rating="project.my_rating" :readonly="false" size="default" @rate="rateProject"
                @clear="clearRating" />
            </div>

            <v-divider class="my-4" />

            <div v-if="availableModes.length > 1" class="mb-3">
              <p class="text-body-2 mb-2">Mode de génération :</p>
              <v-radio-group v-model="pdfMode" inline density="compact" hide-details>
                <v-radio v-for="m in availableModes" :key="m.value" :label="m.label" :value="m.value" />
              </v-radio-group>
            </div>

            <v-btn block color="primary" variant="tonal" prepend-icon="mdi-printer" :loading="downloadingPdf"
              class="mb-2" :disabled="availableModes.length === 0" @click="downloadPdf">
              Télécharger le PDF
            </v-btn>

            <v-btn v-if="project.allow_duplication" block color="success" variant="tonal"
              prepend-icon="mdi-content-copy" :loading="duplicating" @click="duplicate">
              Dupliquer dans mes projets
            </v-btn>

            <v-alert v-else type="info" variant="tonal" density="compact" class="mt-3">
              L'auteur n'a pas autorisé la duplication de ce projet.
            </v-alert>
            <template v-if="canReport">
              <v-divider class="my-4" />
              <v-btn block variant="text" color="error" size="small" prepend-icon="mdi-flag-outline"
                @click="reportOpen = true">
                Signaler ce jeu
              </v-btn>
            </template>
          </v-card>
        </v-col>
      </v-row>

      <div class="hidden-printable">
        <PrintableCards v-if="pdfMode === 'printable'" ref="printableCardsRef" :project="project" />
        <PrintableCheatsheet v-if="pdfMode === 'existing_deck'" ref="printableCheatsheetRef" :project="project" />
      </div>
    </template>
    <ReportModal v-if="project" v-model="reportOpen" reportable-type="project" :reportable-id="project.id"
      :target-label="project.name"
      @reported="showSuccess('Signalement envoyé. Merci, un administrateur examinera votre demande.')" />
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="snackbar.timeout ?? 3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../../api'
import { getUser } from '../../router'
import PrintableCards from '../../components/projects/PrintableCards.vue'
import PrintableCheatsheet from '../../components/projects/PrintableCheatsheet.vue'
import html2pdf from 'html2pdf.js'
import RatingStars from '../../components/common/RatingStars.vue'
import CommentsAccordion from '../../components/common/CommentsAccordion.vue'
import ReportModal from '../../components/common/ReportModal.vue'

const router = useRouter()
const route = useRoute()
const currentUserId = ref(null)
const projectId = route.params.id

const project = ref(null)
const loading = ref(true)
const downloadingPdf = ref(false)
const duplicating = ref(false)
const printableCardsRef = ref(null)
const printableCheatsheetRef = ref(null)
const snackbar = ref({ show: false, message: '', color: 'success', timeout: 3000 })

const pdfMode = ref('printable')
const reportOpen = ref(false)

const isAdmin = ref(false)

const availableModes = computed(() => {
  if (!project.value) return []
  const modes = []
  const slugs = (project.value.formats ?? []).map(f => f.slug)

  if (slugs.includes('impression')) {
    modes.push({ value: 'printable', label: 'Impression' })
  }
  if (slugs.includes('cartes-classiques')) {
    modes.push({ value: 'existing_deck', label: 'Pense-bête' })
  }
  return modes
})
const canReport = computed(() => {
  if (!currentUserId.value || !project.value) return false
  const authorId = project.value.author?.id ?? project.value.user_id
  return authorId !== currentUserId.value
})

function objectCustomFields(object) {
  const schema = project.value?.card_schema ?? []
  return schema.filter(field => {
    const val = object.custom_data?.[field.key]
    return val !== null && val !== undefined && val !== ''
  })
}

function formatFieldValue(field, value) {
  if (field.type === 'boolean') return value ? 'Oui' : 'Non'
  return value
}

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success', timeout: 3000 } }
function showError(msg, timeout = 3000) { snackbar.value = { show: true, message: msg, color: 'error', timeout } }


async function loadProject() {
  loading.value = true
  try {
    const { data } = await api.get(`/community/${projectId}`)
    project.value = data
    if (availableModes.value.length > 0) {
      pdfMode.value = availableModes.value[0].value
    }
  } catch (e) {
    if (e.response?.status === 404) {
      showError('Projet introuvable ou non publié.')
      router.push('/community')
    } else {
      showError('Erreur lors du chargement.')
    }
  } finally {
    loading.value = false
  }
}

async function downloadPdf() {
  downloadingPdf.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 200))

    const element = pdfMode.value === 'printable'
      ? printableCardsRef.value?.printArea
      : printableCheatsheetRef.value?.printArea

    if (!element) {
      showError('Erreur : zone d\'impression introuvable.')
      return
    }

    const options = {
      margin: 0,
      filename: `${project.value.name.replace(/[^a-z0-9]/gi, '_')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['css'], after: '.page' },
    }

    await html2pdf().set(options).from(element).save()

    try {
      await api.post(`/community/${projectId}/play`, { mode: pdfMode.value })
    } catch {
      // silencieux : on n'embête pas l'user si la trace échoue
    }

    showSuccess('PDF téléchargé.')
  } catch (e) {
    showError('Erreur lors de la génération du PDF.')
  } finally {
    downloadingPdf.value = false
  }
}

async function duplicate() {
  duplicating.value = true
  try {
    const { data } = await api.post(`/community/${projectId}/duplicate`)
    showSuccess('Projet dupliqué. Redirection…')
    setTimeout(() => router.push(`/projects/${data.id}/edit`), 600)
  } catch (e) {
    if (e.response?.status === 403) {
      showError(e.response.data.message ?? 'Duplication non autorisée.', 6000)
    } else {
      showError('Erreur lors de la duplication.')
    }
  } finally {
    duplicating.value = false
  }
}
async function rateProject(score) {
  try {
    await api.post('/ratings', { project_id: project.value.id, score })
    showSuccess('Note enregistrée.')
    await loadProject()
  } catch (e) {
    showError('Erreur lors de la notation.')
  }
}

async function clearRating() {
  try {
    await api.delete('/ratings', { data: { project_id: project.value.id } })
    showSuccess('Note retirée.')
    await loadProject()
  } catch {
    showError('Erreur lors du retrait de la note.')
  }
}

onMounted(async () => {
  const u = await getUser()
  currentUserId.value = u?.id ?? null
  isAdmin.value = u?.role === 'admin'
  loadProject()
})
</script>

<style scoped>
.sticky-top {
  position: sticky;
  top: 80px;
}

.rules-text {
  white-space: pre-wrap;
  font-family: inherit;
  margin: 0;
}

.hidden-printable {
  position: absolute;
  left: -10000px;
  top: 0;
}

.custom-fields {
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  padding-top: 6px;
  margin-top: 4px;
  font-size: 0.75rem;
  text-align: left;
}

.custom-field {
  margin-bottom: 2px;
  display: flex;
  justify-content: space-between;
  gap: 6px;
}

.custom-field-label {
  font-weight: 600;
  opacity: 0.85;
}

.custom-field-value {
  text-align: right;
}
</style>