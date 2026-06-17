<template>
  <v-container>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4">Mes projets</h1>
      <v-btn color="primary" prepend-icon="mdi-plus" to="/games">
        Créer un projet
      </v-btn>
    </div>

    <v-card class="pa-4 mb-4">
      <div class="d-flex flex-wrap ga-3 align-end">
        <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" label="Rechercher" variant="outlined"
          density="compact" hide-details clearable style="max-width: 280px;" />
        <v-select v-model="statusFilter" :items="statusOptions" label="Statut" variant="outlined" density="compact"
          hide-details clearable style="max-width: 200px;" />
        <v-select v-model="completenessFilter" :items="completenessOptions" label="Qualité" variant="outlined"
          density="compact" hide-details clearable style="max-width: 200px;" />
      </div>
    </v-card>

    <v-data-table :headers="headers" :items="filteredProjects" :loading="loading" :no-data-text="emptyMessage"
      loading-text="Chargement…" :items-per-page="25" :items-per-page-options="[10, 25, 50, 100]">

      <template #item.name="{ item }">
        <RouterLink :to="`/projects/${item.id}`" class="text-decoration-none font-weight-medium">
          {{ item.name }}
        </RouterLink>
      </template>

      <template #item.status="{ item }">
        <v-chip size="small" :color="statusConfig[item.status]?.color" label>
          {{ statusConfig[item.status]?.label ?? item.status }}
        </v-chip>
      </template>

      <template #item.publishable="{ item }">
        <v-chip size="small" :color="item.publishable?.ready ? 'success' : 'warning'" label>
          {{ item.publishable?.ready ? 'Complet' : 'Incomplet' }}
        </v-chip>
      </template>

      <template #item.mode="{ item }">
        <span class="text-body-2">{{ modeLabel(item.mode) }}</span>
      </template>

      <template #item.updated_at="{ item }">
        <span class="text-body-2">{{ formatDate(item.updated_at) }}</span>
      </template>

      <template #item.actions="{ item }">
        <v-btn icon="mdi-eye" size="small" variant="text" :to="`/projects/${item.id}`" />
        <v-btn icon="mdi-pencil" size="small" variant="text" :to="`/projects/${item.id}/edit`" />
        <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="askDelete(item)" />
      </template>
    </v-data-table>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
    <v-dialog v-model="deleteDialog" max-width="440">
      <v-card>
        <v-card-title>Supprimer le projet</v-card-title>
        <v-card-text>
          Voulez-vous vraiment supprimer <strong>{{ projectToDelete?.name }}</strong> ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="deleting" @click="confirmDelete">Supprimer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../api'
import { PROJECT_STATUS } from '../../i18n/status.js'

const projects = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref(null)
const completenessFilter = ref(null)
const snackbar = ref({ show: false, message: '', color: 'success' })

const deleteDialog = ref(false)
const projectToDelete = ref(null)
const deleting = ref(false)

const statusConfig = PROJECT_STATUS

const statusOptions = [
  { title: 'Brouillon', value: 'draft' },
  { title: 'Publié', value: 'published' },
  { title: 'Archivé', value: 'archived' },
]

const completenessOptions = [
  { title: 'Complet', value: 'complete' },
  { title: 'Incomplet', value: 'incomplete' },
]

const headers = [
  { title: 'Nom', key: 'name', sortable: true },
  { title: 'Statut', key: 'status', sortable: true, width: 130 },
  { title: 'Qualité', key: 'publishable', sortable: false, width: 130 },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Modèle', key: 'template', sortable: true },
  { title: 'Mode', key: 'mode', sortable: true, width: 140 },
  { title: 'Dernière modif', key: 'updated_at', sortable: true, width: 150 },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: 100 },
]

const filteredProjects = computed(() => {
  let list = projects.value

  if (search.value?.trim()) {
    const s = search.value.toLowerCase().trim()
    list = list.filter(p =>
      p.name?.toLowerCase().includes(s) ||
      p.description?.toLowerCase().includes(s) ||
      p.template?.toLowerCase().includes(s)
    )
  }

  if (statusFilter.value) {
    list = list.filter(p => p.status === statusFilter.value)
  }

  if (completenessFilter.value === 'complete') {
    list = list.filter(p => p.publishable?.ready)
  } else if (completenessFilter.value === 'incomplete') {
    list = list.filter(p => !p.publishable?.ready)
  }

  return list
})

const emptyMessage = computed(() => {
  if (projects.value.length === 0) return 'Vous n\'avez aucun projet pour l\'instant.'
  return 'Aucun projet ne correspond à votre recherche.'
})

function modeLabel(mode) {
  if (mode === 'printable') return 'Impression'
  if (mode === 'existing_deck') return 'Pense-bête'
  return mode
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

async function loadProjects() {
  loading.value = true
  try {
    const { data } = await api.get('/projects')
    projects.value = data
  } catch (e) {
    snackbar.value = { show: true, message: 'Erreur lors du chargement.', color: 'error' }
  } finally {
    loading.value = false
  }
}
function askDelete(item) {
  projectToDelete.value = item
  deleteDialog.value = true
}
async function confirmDelete() {
  if (!projectToDelete.value) return
  deleting.value = true
  try {
    await api.delete(`/projects/${projectToDelete.value.id}`)
    snackbar.value = { show: true, message: 'Projet supprimé.', color: 'success' }
    deleteDialog.value = false
    await loadProjects()
  } catch (e) {
    snackbar.value = { show: true, message: 'Erreur lors de la suppression.', color: 'error' }
  } finally {
    deleting.value = false
  }
}
onMounted(loadProjects)
</script>