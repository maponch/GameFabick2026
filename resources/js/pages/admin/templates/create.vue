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
      <TemplateForm ref="formRef" v-model="form" :types="types" :types-loading="typesLoading" :server-errors="errors" />
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
import { ref, onMounted } from 'vue'
import { api } from '../../../api'
import { useRouter } from 'vue-router'
import TemplateForm from '../../../components/admin/templates/TemplateForm.vue'

const router = useRouter()

const formRef = ref(null)
const types = ref([])
const typesLoading = ref(true)
const saving = ref(false)
const errors = ref({})
const snackbar = ref({ show: false, message: '', color: 'success' })

const form = ref({
  name: '',
  description: '',
  rules: '',
  type_id: null,
  min_players: 1,
  max_players: 4,
  duration_min: 15,
  duration_max: 30,
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

async function submit() {
  errors.value = {}
  const valid = await formRef.value.validate()
  if (!valid) {
    showError('Veuillez corriger les champs en rouge.')
    return
  }

  saving.value = true
  try {
    const { data } = await api.post('/admin/templates', form.value)
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

onMounted(loadTypes)
</script>