<template>
  <v-container max-width="900" class="py-6">
    <div class="d-flex align-center ga-3 mb-4">
      <v-btn icon="mdi-arrow-left" variant="text" @click="$router.push('/projects')" />
      <h1 class="text-h4">Créer un projet</h1>
    </div>

    <p class="text-body-2 text-medium-emphasis mb-4">
      Créez votre projet de A à Z. Vous pourrez ajouter les cartes et personnaliser le détail après.
    </p>

    <v-card>
      <v-stepper v-model="step" alt-labels hide-actions>
        <v-stepper-header>
          <v-stepper-item :complete="step > 1" :value="1" title="Identité" />
          <v-divider />
          <v-stepper-item :complete="step > 2" :value="2" title="Configuration" />
          <v-divider />
          <v-stepper-item :complete="step > 3" :value="3" title="Apparence" />
        </v-stepper-header>

        <v-stepper-window>
          <!-- Étape 1 — Identité -->
          <v-stepper-window-item :value="1">
            <div class="pa-4">
              <v-text-field v-model="form.name" label="Nom du projet *" variant="outlined" :error-messages="errors.name"
                maxlength="191" counter class="mb-3" />
              <v-textarea v-model="form.description" label="Description" variant="outlined" rows="3"
                :error-messages="errors.description" maxlength="2000" counter class="mb-3" />
              <v-select v-model="form.type_id" :items="types" item-title="name" item-value="id" label="Type de jeu *"
                variant="outlined" :loading="typesLoading" :error-messages="errors.type_id" />
            </div>
          </v-stepper-window-item>

          <!-- Étape 2 — Configuration -->
          <v-stepper-window-item :value="2">
            <div class="pa-4">
              <h3 class="text-subtitle-1 mb-2">Nombre de joueurs</h3>
              <div class="d-flex ga-3 mb-4">
                <v-text-field v-model.number="form.min_players" label="Minimum *" type="number" variant="outlined"
                  :min="1" :max="50" :error-messages="errors.min_players" />
                <v-text-field v-model.number="form.max_players" label="Maximum *" type="number" variant="outlined"
                  :min="form.min_players || 1" :max="50" :error-messages="errors.max_players" />
              </div>

              <h3 class="text-subtitle-1 mb-2">Durée d'une partie (minutes)</h3>
              <div class="d-flex ga-3 mb-4">
                <v-text-field v-model.number="form.duration_min" label="Minimum *" type="number" variant="outlined"
                  :min="1" :max="600" :error-messages="errors.duration_min" />
                <v-text-field v-model.number="form.duration_max" label="Maximum *" type="number" variant="outlined"
                  :min="form.duration_min || 1" :max="600" :error-messages="errors.duration_max" />
              </div>

              <h3 class="text-subtitle-1 mb-2">Formats supportés *</h3>
              <p class="text-caption text-medium-emphasis mb-2">
                Indique sur quels supports votre jeu peut se jouer (au moins un).
              </p>
              <div class="mb-2">
                <div v-if="formatsLoading" class="d-flex justify-center pa-3">
                  <v-progress-circular indeterminate size="24" color="primary" />
                </div>
                <div v-else class="d-flex flex-wrap ga-2">
                  <v-chip v-for="f in formats" :key="f.id" :color="form.format_ids.includes(f.id) ? 'primary' : ''"
                    :variant="form.format_ids.includes(f.id) ? 'flat' : 'outlined'"
                    :prepend-icon="form.format_ids.includes(f.id) ? 'mdi-check' : 'mdi-plus'" class="cursor-pointer"
                    @click="toggleFormat(f.id)">
                    {{ f.name }}
                  </v-chip>
                </div>
                <div v-if="errors.format_ids" class="text-error text-caption mt-2">
                  {{ Array.isArray(errors.format_ids) ? errors.format_ids[0] : errors.format_ids }}
                </div>
              </div>
            </div>
          </v-stepper-window-item>

          <!-- Étape 3 — Apparence -->
          <v-stepper-window-item :value="3">
            <div class="pa-4">
              <h3 class="text-subtitle-1 mb-2">Apparence des cartes</h3>
              <p class="text-caption text-medium-emphasis mb-4">
                Choisissez un modèle prédéfini ou commencez avec un schéma vide
                (vous définirez vos propres champs après création).
              </p>

              <v-row>
                <v-col v-for="layout in cardLayouts" :key="layout.slug" cols="12" sm="6">
                  <v-card :variant="form.card_layout === layout.slug ? 'tonal' : 'outlined'"
                    :color="form.card_layout === layout.slug ? 'primary' : ''" class="pa-4 cursor-pointer h-100"
                    @click="form.card_layout = layout.slug">
                    <div class="d-flex align-center ga-2 mb-2">
                      <v-icon>mdi-card-text-outline</v-icon>
                      <strong>{{ layout.name }}</strong>
                    </div>
                    <p v-if="layout.description" class="text-body-2 text-medium-emphasis mb-0">
                      {{ layout.description }}
                    </p>
                  </v-card>
                </v-col>

                <v-col cols="12" sm="6">
                  <v-card :variant="form.card_layout === null ? 'tonal' : 'outlined'"
                    :color="form.card_layout === null ? 'primary' : ''" class="pa-4 cursor-pointer h-100"
                    @click="form.card_layout = null">
                    <div class="d-flex align-center ga-2 mb-2">
                      <v-icon>mdi-pencil-ruler</v-icon>
                      <strong>Schéma libre</strong>
                    </div>
                    <p class="text-body-2 text-medium-emphasis mb-0">
                      Définissez vous-même les champs de vos cartes après création.
                    </p>
                  </v-card>
                </v-col>
              </v-row>
            </div>
          </v-stepper-window-item>
        </v-stepper-window>

        <v-divider />

        <div class="pa-4 d-flex justify-space-between">
          <v-btn variant="text" :disabled="step === 1" @click="step--">
            Précédent
          </v-btn>

          <v-btn v-if="step < 3" color="primary" :disabled="!canGoNext" @click="step++">
            Suivant
          </v-btn>
          <v-btn v-else color="success" :loading="submitting" :disabled="!canSubmit" @click="submit">
            Créer le projet
          </v-btn>
        </div>
      </v-stepper>
    </v-card>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'

const router = useRouter()

const step = ref(1)
const submitting = ref(false)
const errors = ref({})
const snackbar = ref({ show: false, message: '', color: 'success' })

const types = ref([])
const typesLoading = ref(true)
const formats = ref([])
const formatsLoading = ref(true)
const cardLayouts = ref([])

const form = ref({
  name: '',
  description: '',
  type_id: null,
  min_players: 2,
  max_players: 4,
  duration_min: 15,
  duration_max: 30,
  format_ids: [],
  card_layout: null,
})

const canGoNext = computed(() => {
  if (step.value === 1) {
    return !!form.value.name?.trim() && !!form.value.type_id
  }
  if (step.value === 2) {
    return form.value.min_players >= 1
      && form.value.max_players >= form.value.min_players
      && form.value.duration_min >= 1
      && form.value.duration_max >= form.value.duration_min
      && form.value.format_ids.length > 0
  }
  return true
})

const canSubmit = computed(() => canGoNext.value && !submitting.value)

function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

async function loadReferentials() {
  try {
    const [typesRes, formatsRes, layoutsRes] = await Promise.all([
      api.get('/types'),
      api.get('/formats'),
      api.get('/card-layouts'),
    ])
    types.value = typesRes.data
    formats.value = formatsRes.data
    cardLayouts.value = layoutsRes.data
  } catch {
    showError('Erreur lors du chargement des référentiels.')
  } finally {
    typesLoading.value = false
    formatsLoading.value = false
  }
}
function toggleFormat(id) {
  const idx = form.value.format_ids.indexOf(id)
  if (idx === -1) form.value.format_ids.push(id)
  else form.value.format_ids.splice(idx, 1)
}

async function submit() {
  errors.value = {}
  submitting.value = true
  try {
    const { data } = await api.post('/projects/free', form.value)
    router.push(`/projects/${data.id}/edit`)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      showError('Veuillez corriger les champs en rouge.')
      // Revenir à la première étape qui a des erreurs
      if (errors.value.name || errors.value.description || errors.value.type_id) step.value = 1
      else if (errors.value.min_players || errors.value.max_players
        || errors.value.duration_min || errors.value.duration_max
        || errors.value.format_ids) step.value = 2
    } else {
      showError('Erreur lors de la création.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(loadReferentials)
</script>
<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>