<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="$router.push('/admin')" />
      <h1 class="text-h4">Modération des projets</h1>
    </div>

    <p class="text-medium-emphasis mb-4">
      Liste des projets publiés. Si un projet enfreint les règles, vous pouvez l'archiver.
      L'utilisateur recevra un email avec le motif et pourra le désarchiver pour le corriger.
    </p>

    <v-card class="pa-4 mb-4">
      <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" label="Rechercher" variant="outlined"
        density="compact" hide-details clearable style="max-width: 320px;" />
    </v-card>

    <v-data-table :headers="headers" :items="filteredProjects" :loading="loading" :no-data-text="emptyMessage"
      loading-text="Chargement…" :items-per-page="25" :items-per-page-options="[10, 25, 50, 100]">

      <template #item.name="{ item }">
        <RouterLink :to="`/community/${item.id}`" class="text-decoration-none font-weight-medium">
          {{ item.name }}
        </RouterLink>
      </template>

      <template #item.user="{ item }">
        <div class="text-body-2">
          <div>{{ item.user.username }}</div>
          <div class="text-caption text-medium-emphasis">{{ item.user.email }}</div>
        </div>
      </template>

      <template #item.moderation_count="{ item }">
        <v-chip v-if="item.moderation_count > 0" size="small" color="warning" label>
          {{ item.moderation_count }}
        </v-chip>
        <span v-else class="text-medium-emphasis text-body-2">—</span>
      </template>

      <template #item.updated_at="{ item }">
        <span class="text-body-2">{{ formatDate(item.updated_at) }}</span>
      </template>

      <template #item.actions="{ item }">
        <v-btn icon="mdi-eye" size="small" variant="text" :to="`/projects/${item.id}`" />
        <v-btn icon="mdi-archive" size="small" variant="text" color="warning" @click="openModerate(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="modal" max-width="520" persistent>
      <v-card>
        <v-card-title>Archiver le projet</v-card-title>
        <v-card-text>
          <p class="mb-3">
            Confirmer l'archivage de <strong>{{ projectToModerate?.name }}</strong>.
            L'utilisateur recevra une notification par email.
          </p>

          <v-select v-model="reasonCode" :items="reasonOptions" label="Motif *" variant="outlined"
            :rules="[v => !!v || 'Motif requis']" :error-messages="errors.reason_code" class="mb-3" />

          <v-textarea v-model="reasonText"
            :label="reasonCode === 'other' ? 'Précision (obligatoire) *' : 'Précision (optionnelle)'" variant="outlined"
            rows="3" :rules="reasonTextRules" :error-messages="errors.reason_text" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" :disabled="submitting" @click="closeModerate">Annuler</v-btn>
          <v-btn color="warning" :loading="submitting" @click="submitModerate">Archiver</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="snackbar.timeout ?? 3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../../api'

const projects = ref([])
const loading = ref(true)
const search = ref('')
const snackbar = ref({ show: false, message: '', color: 'success', timeout: 3000 })

const modal = ref(false)
const projectToModerate = ref(null)
const reasonCode = ref(null)
const reasonText = ref('')
const submitting = ref(false)
const errors = ref({})

const reasonOptions = [
  { title: 'Spam ou publicité', value: 'spam' },
  { title: 'Contenu inapproprié', value: 'inappropriate' },
  { title: 'Qualité insuffisante', value: 'low_quality' },
  { title: 'Violation de droits d\'auteur', value: 'copyright' },
  { title: 'Autre', value: 'other' },
]

const reasonTextRules = computed(() => {
  if (reasonCode.value === 'other') {
    return [v => !!v?.trim() || 'Précision obligatoire pour le motif "Autre"']
  }
  return []
})

const headers = [
  { title: 'Nom', key: 'name', sortable: true },
  { title: 'Utilisateur', key: 'user', sortable: false },
  { title: 'Type', key: 'type', sortable: true, width: 130 },
  { title: 'Modèle', key: 'template', sortable: true },
  { title: 'Modérations passées', key: 'moderation_count', sortable: true, width: 180, align: 'center' },
  { title: 'Mise à jour', key: 'updated_at', sortable: true, width: 150 },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: 100 },
]

const filteredProjects = computed(() => {
  if (!search.value?.trim()) return projects.value
  const s = search.value.toLowerCase().trim()
  return projects.value.filter(p =>
    p.name?.toLowerCase().includes(s) ||
    p.description?.toLowerCase().includes(s) ||
    p.user?.username?.toLowerCase().includes(s) ||
    p.user?.email?.toLowerCase().includes(s)
  )
})

const emptyMessage = computed(() => {
  if (projects.value.length === 0) return 'Aucun projet publié pour l\'instant.'
  return 'Aucun projet ne correspond à votre recherche.'
})

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function showSuccess(msg) {
  snackbar.value = { show: true, message: msg, color: 'success', timeout: 3000 }
}

function showError(msg, timeout = 3000) {
  snackbar.value = { show: true, message: msg, color: 'error', timeout }
}

async function loadProjects() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/projects')
    projects.value = data
  } catch (e) {
    showError('Erreur lors du chargement.')
  } finally {
    loading.value = false
  }
}

function openModerate(item) {
  projectToModerate.value = item
  reasonCode.value = null
  reasonText.value = ''
  errors.value = {}
  modal.value = true
}

function closeModerate() {
  modal.value = false
}

async function submitModerate() {
  if (!projectToModerate.value || !reasonCode.value) {
    showError('Veuillez choisir un motif.')
    return
  }
  if (reasonCode.value === 'other' && !reasonText.value?.trim()) {
    showError('Une précision est obligatoire pour le motif "Autre".')
    return
  }

  errors.value = {}
  submitting.value = true
  try {
    await api.post(`/admin/projects/${projectToModerate.value.id}/moderate`, {
      reason_code: reasonCode.value,
      reason_text: reasonText.value?.trim() || null,
    })
    showSuccess('Projet archivé et utilisateur notifié.')
    modal.value = false
    await loadProjects()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      showError('Action refusée par le serveur.', 6000)
    } else {
      showError('Erreur lors de la modération.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(loadProjects)
</script>