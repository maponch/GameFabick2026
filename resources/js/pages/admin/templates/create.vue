<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" />
      <h1 class="text-h4">Nouveau template</h1>
    </div>

    <p class="text-medium-emphasis mb-4">
      Renseignez les informations de base. Vous pourrez ajouter les cartes,
      les champs personnalisés et publier le jeu à l'étape suivante.
    </p>
    <v-card class="pa-4 mb-4">
      <v-card-title class="px-0">Mode du template</v-card-title>
      <p class="text-medium-emphasis text-body-2 mb-3">
        Le mode est figé à la création et ne peut plus être modifié ensuite.
      </p>

      <v-radio-group v-model="form.mode" inline density="compact" hide-details class="mb-3">
        <v-radio label="Schéma libre" value="free" />
        <v-radio label="Modèle prédéfini" value="preset" />
      </v-radio-group>

      <v-select v-if="form.mode === 'preset'" v-model="form.card_layout" :items="layouts" item-title="name"
        item-value="slug" label="Choisir un modèle *" variant="outlined" :loading="layoutsLoading"
        :rules="[rules.required]" :error-messages="errors.card_layout" :hint="selectedLayoutDescription"
        persistent-hint />
    </v-card>
    <v-card class="pa-4 mb-4">
      <TemplateForm ref="formRef" v-model="form" :types="types" :types-loading="typesLoading" :formats="formats"
        :formats-loading="formatsLoading" :server-errors="errors" />
    </v-card>

    <div class="d-flex ga-2">
      <v-btn variant="text" @click="goBack">Annuler</v-btn>
      <v-spacer />
      <v-btn color="primary" :loading="saving" @click="submit">Créer le brouillon</v-btn>
    </div>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../../api'
import { useRouter } from 'vue-router'
import TemplateForm from '../../../components/common/TemplateForm.vue'

const router = useRouter()

const formRef = ref(null)
const types = ref([])
const typesLoading = ref(true)
const saving = ref(false)
const errors = ref({})
const snackbar = ref({ show: false, message: '', color: 'success' })
const formats = ref([])
const formatsLoading = ref(true)

const form = ref({
  name: '',
  description: '',
  rules: '',
  type_id: null,
  format_ids: [],
  min_players: 1,
  max_players: 4,
  duration_min: 15,
  duration_max: 30,
  mode: 'free',
  card_layout: null,
})

const layouts = ref([])
const layoutsLoading = ref(true)

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
}

const selectedLayoutDescription = computed(() => {
  if (form.value.mode !== 'preset' || !form.value.card_layout) return ''
  const l = layouts.value.find(l => l.slug === form.value.card_layout)
  return l?.description ?? ''
})

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

async function loadTypes() {
  typesLoading.value = true
  try {
    const { data } = await api.get('/admin/types')
    types.value = data
  } catch (e) {
    showError('Erreur lors du chargement des types.')
  } finally {
    typesLoading.value = false
  }
}
async function loadFormats() {
  formatsLoading.value = true
  try {
    const { data } = await api.get('/admin/formats')
    formats.value = data
  } catch (e) {
    showError('Erreur lors du chargement des formats.')
  } finally {
    formatsLoading.value = false
  }
}
async function loadLayouts() {
  layoutsLoading.value = true
  try {
    const { data } = await api.get('/admin/card-layouts')
    layouts.value = data
  } catch (e) {
    showError('Erreur lors du chargement des modèles.')
  } finally {
    layoutsLoading.value = false
  }
}

async function submit() {
  errors.value = {}
  const valid = await formRef.value.validate()
  if (!valid) {
    showError('Veuillez corriger les champs en rouge.')
    return
  }

  saving.value = true
  try {
    const payload = { ...form.value }
    if (payload.mode === 'free') {
      payload.card_layout = null
    }
    delete payload.mode

    const { data } = await api.post('/admin/templates', payload)
    showSuccess('Brouillon créé.')
    router.push(`/admin/templates/${data.id}/edit`)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      showError('Le serveur a rejeté certaines valeurs.')
    } else if (e.response?.status === 403) {
      showError('Action non autorisée.')
    } else {
      showError('Erreur lors de la création.')
    }
  } finally {
    saving.value = false
  }
}

function goBack() { router.push('/admin/templates') }

onMounted(async () => {
  loadTypes()
  loadFormats()
  loadLayouts()
})
</script>